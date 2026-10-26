<?php

namespace App\Services;

use App\Enums\ProductionOrderStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Inventory\InventoryBalance;
use App\Models\Inventory\InventoryReservation;
use App\Models\Production\ProductionOrder;
use App\Models\Purchasing\PurchaseOrder;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\System\AuditLog;
use BackedEnum;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 状态自动转换服务
 *
 * 本项目的状态流转规则分散在各 Model 的 canXxx() 守卫与 Service 的转换方法里，
 * 其中一部分跃迁并不是人在界面点了按钮才发生，而是由「数量达标 / 到期 / 物料重新齐套」
 * 这类条件驱动的，例如：
 *
 *   · 销售订单 delivered_quantity >= total_quantity → 已发货（SalesOrderService::updateDelivery）
 *   · 采购订单 received_quantity  >= total_quantity → 已收货（PurchaseOrderService::updateReceiving）
 *   · 生产工单 缺料齐套后由 material_shortage → ready（ProductionOrderService::release 的分支）
 *   · 报价单   过了有效期 → expired（QuotationStatus::Expired 在此之前没有任何写入方）
 *   · 库存预留 过了到期日 → expired，并释放占用量
 *
 * 一旦上述事件驱动路径被绕过（数据导入、直接改库、异步失败、跨模块事务回滚等），
 * 单据就会永久卡在中间状态。本服务做的正是「补算」：定时重新求值这些条件，
 * 只补渐进方向（中间态 → 后继态），不回退人工已完成的单据，也不新增状态或字段。
 */
class StateTransitionService
{
    /** 审计日志使用的 action，便于与人工操作区分 */
    public const ACTION = 'auto_transition';

    /** 库存预留的状态字面值（该模型目前没有常量定义，与迁移文件默认值保持一致） */
    public const RESERVATION_ACTIVE = 'active';
    public const RESERVATION_EXPIRED = 'expired';

    /** 支持自动转换的模块，键用于命令行 --module 过滤 */
    public static function modules(): array
    {
        return [
            'quotation' => '报价单',
            'production_order' => '生产工单',
            'sales_order' => '销售订单',
            'purchase_order' => '采购订单',
            'inventory_reservation' => '库存预留',
        ];
    }

    public function __construct(protected ProductionOrderService $productionOrders)
    {
    }

    /**
     * 扫描并执行自动转换。
     *
     * @param  int    $limit    每个模块单次最多处理的记录数，防止长事务锁表
     * @param  array  $modules  限定模块，留空表示全部
     * @param  bool   $dryRun   为真时只求值不写库（用于灰度验证规则）
     * @return array<string, array{name:string, duration_ms:int, transitions:array, error:?string}>
     */
    public function scan(int $limit = 200, array $modules = [], bool $dryRun = false): array
    {
        $limit = max(1, $limit);
        $modules = collect($modules ?: array_keys(self::modules()))
            ->filter(fn ($module) => array_key_exists($module, self::modules()))
            ->unique()
            ->values()
            ->all();

        $report = [];

        foreach ($modules as $module) {
            $started = microtime(true);

            try {
                $transitions = match ($module) {
                    'quotation' => $this->expireQuotations($limit, $dryRun),
                    'production_order' => $this->releaseMaterialShortage($limit, $dryRun),
                    'sales_order' => $this->reconcileSalesDelivery($limit, $dryRun),
                    'purchase_order' => $this->reconcilePurchaseReceiving($limit, $dryRun),
                    'inventory_reservation' => $this->expireReservations($limit, $dryRun),
                    default => [],
                };
                $error = null;
            } catch (\Throwable $e) {
                $transitions = [];
                $error = $e->getMessage();

                Log::error('状态自动转换扫描异常', [
                    'module' => $module,
                    'error' => $e->getMessage(),
                ]);
            }

            $report[$module] = [
                'name' => self::modules()[$module] ?? $module,
                'duration_ms' => (int) round((microtime(true) - $started) * 1000),
                'transitions' => $transitions,
                'error' => $error,
            ];
        }

        return $report;
    }

    /**
     * 报价单过了有效期自动失效。
     *
     * QuotationStatus::Expired 此前没有任何写入方（枚举里定义了却没人用），
     * 这里按「已过 valid_until 且未成交/未作废」显式收敛；
     * won / converted / lost / cancelled 为终态，不参与。
     */
    protected function expireQuotations(int $limit, bool $dryRun): array
    {
        $quotations = Quotation::query()
            ->whereIn('status', [
                QuotationStatus::Draft->value,
                QuotationStatus::Submitted->value,
            ])
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', '<', now()->startOfDay())
            ->orderBy('valid_until')
            ->limit($limit)
            ->get();

        $transitions = [];

        foreach ($quotations as $quotation) {
            $transitions[] = $this->apply(
                $quotation,
                'quotation',
                'status',
                QuotationStatus::Expired,
                sprintf('报价单有效期已于 %s 到期，自动失效', $quotation->valid_until),
                $dryRun
            );
        }

        return $transitions;
    }

    /**
     * 缺料工单重新齐套后自动回到待开工。
     *
     * 与 ProductionOrderService::release() 使用同一套缺料判定：
     * 该工单行未发料需求 > 可用库存即视为短缺，无短缺即可开工。
     * 反向（ready → material_shortage）不做：下达成功后再被占用属于正常竞争，
     * 由开工/发料环节自行拦截，避免定时任务把已下达的工单打回。
     */
    protected function releaseMaterialShortage(int $limit, bool $dryRun): array
    {
        $orders = ProductionOrder::query()
            ->where('status', ProductionOrderStatus::MaterialShortage->value)
            ->with('lines.item')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $transitions = [];

        foreach ($orders as $order) {
            // 缺料判定以各个工单行的需求为准，任一物料仍不足就继续等待
            $shortage = $this->productionOrders->checkMaterialShortage($order);

            if (! empty($shortage)) {
                continue;
            }

            $transitions[] = $this->apply(
                $order,
                'production_order',
                'status',
                ProductionOrderStatus::Ready,
                '物料已齐套，自动恢复为待开工',
                $dryRun
            );
        }

        return $transitions;
    }

    /**
     * 销售订单发货状态对账。
     *
     * 规则与 SalesOrderService::updateDelivery() 完全一致：
     * 已发数量 ≥ 订单数量 → 已发货；已发数量 > 0 → 部分发货。
     * 这里只处理「已审核 / 部分发货」的单据，终态（已发货、已关闭、已取消）不动。
     */
    protected function reconcileSalesDelivery(int $limit, bool $dryRun): array
    {
        $orders = SalesOrder::query()
            ->whereIn('status', [
                SalesOrderStatus::Approved->value,
                SalesOrderStatus::PartiallyDelivered->value,
            ])
            ->where('delivered_quantity', '>', 0)
            ->where('total_quantity', '>', 0)
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $transitions = [];

        foreach ($orders as $order) {
            $target = (float) $order->delivered_quantity >= (float) $order->total_quantity
                ? SalesOrderStatus::FullyDelivered
                : SalesOrderStatus::PartiallyDelivered;

            if ($order->status === $target) {
                continue;
            }

            $transitions[] = $this->apply(
                $order,
                'sales_order',
                'status',
                $target,
                sprintf(
                    '发货数量校对：已发 %s / 订单 %s，自动推进发货状态',
                    $order->delivered_quantity,
                    $order->total_quantity
                ),
                $dryRun
            );
        }

        return $transitions;
    }

    /**
     * 采购订单收货状态对账（规则同 PurchaseOrderService::updateReceiving()）。
     */
    protected function reconcilePurchaseReceiving(int $limit, bool $dryRun): array
    {
        $orders = PurchaseOrder::query()
            ->whereIn('status', [
                PurchaseOrderStatus::Approved->value,
                PurchaseOrderStatus::PartiallyReceived->value,
            ])
            ->where('received_quantity', '>', 0)
            ->where('total_quantity', '>', 0)
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $transitions = [];

        foreach ($orders as $order) {
            $target = (float) $order->received_quantity >= (float) $order->total_quantity
                ? PurchaseOrderStatus::FullyReceived
                : PurchaseOrderStatus::PartiallyReceived;

            if ($order->status === $target) {
                continue;
            }

            $transitions[] = $this->apply(
                $order,
                'purchase_order',
                'status',
                $target,
                sprintf(
                    '收货数量校对：已收 %s / 订单 %s，自动推进收货状态',
                    $order->received_quantity,
                    $order->total_quantity
                ),
                $dryRun
            );
        }

        return $transitions;
    }

    /**
     * 库存预留到期：active → expired，并同步释放库存余额上的占用量。
     *
     * 预留记录本身就是「占位」，到期后如果不把 reserved_quantity 减回去，
     * 可用库存会被永久扣减（InventoryBalance::available_quantity = quantity - reserved - frozen）。
     */
    protected function expireReservations(int $limit, bool $dryRun): array
    {
        $reservations = InventoryReservation::query()
            ->where('status', self::RESERVATION_ACTIVE)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', now()->startOfDay())
            ->orderBy('expiry_date')
            ->limit($limit)
            ->get();

        $transitions = [];

        foreach ($reservations as $reservation) {
            $transitions[] = $this->apply(
                $reservation,
                'inventory_reservation',
                'status',
                self::RESERVATION_EXPIRED,
                '预留到期，自动失效并释放占用库存',
                $dryRun,
                function () use ($reservation) {
                    $this->releaseReservedQuantity($reservation);
                }
            );
        }

        return $transitions;
    }

    /**
     * 把预留占用的数量从库存余额上扣回（不足则清零，避免负占用）。
     */
    protected function releaseReservedQuantity(InventoryReservation $reservation): void
    {
        $balance = InventoryBalance::query()
            ->where('item_id', $reservation->item_id)
            ->where('warehouse_id', $reservation->warehouse_id)
            ->where('reserved_quantity', '>', 0)
            ->where(function ($query) use ($reservation) {
                empty($reservation->batch_number)
                    ? $query->whereNull('batch_number')
                    : $query->where('batch_number', $reservation->batch_number);
            })
            ->orderBy('id')
            ->first();

        if (! $balance) {
            return;
        }

        $reserved = (float) $balance->reserved_quantity;

        $balance->reserved_quantity = max(0.0, $reserved - min($reserved, (float) $reservation->quantity));
        $balance->save();
    }

    /**
     * 写入一次状态跃迁，并留下审计日志（user_name 标记为 scheduler，便于与人工操作区分）。
     *
     * @param  BackedEnum|string  $to  目标状态
     * @param  Closure|null       $sideEffect  同一事务内的附加动作，例如释放库存占用
     */
    protected function apply(
        Model $record,
        string $module,
        string $field,
        BackedEnum|string $to,
        string $remark,
        bool $dryRun = false,
        ?Closure $sideEffect = null
    ): array {
        /** @var BackedEnum|string|null $from */
        $from = $record->{$field};

        $descriptor = [
            'id' => $record->getKey(),
            'field' => $field,
            'from' => $this->statusValue($from),
            'to' => $this->statusValue($to),
            'remark' => $remark,
        ];

        if ($dryRun) {
            return $descriptor;
        }

        DB::transaction(function () use ($record, $field, $to, $module, $remark, $from, $sideEffect) {
            $record->{$field} = $to;
            $record->save();

            if ($sideEffect) {
                $sideEffect();
            }

            AuditLog::create([
                'action' => self::ACTION,
                'module' => $module,
                'user_name' => 'scheduler',
                'record_id' => $record->getKey(),
                'record_type' => $record::class,
                'old_values' => [$field => $this->statusValue($from)],
                'new_values' => [$field => $this->statusValue($to)],
                'remark' => $remark,
                'ip_address' => 'console',
                'user_agent' => 'erp:state-transition',
            ]);
        });

        Log::info('状态自动转换', array_merge(['module' => $module], $descriptor));

        return $descriptor;
    }

    /**
     * 状态值统一转标量，便于写日志与审计（枚举取 value，裸字符串原样使用）。
     */
    protected function statusValue(mixed $status): ?string
    {
        if ($status instanceof BackedEnum) {
            return (string) $status->value;
        }

        return $status === null ? null : (string) $status;
    }
}
