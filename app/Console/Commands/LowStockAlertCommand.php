<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * 低库存预警
 *
 * 按「物料 + 仓库」汇总可用库存，低于该物料安全库存即汇总成一条站内通知发给仓储角色，
 * 避免逐条通知刷屏。属于业务通知，不走系统告警通道。
 *
 * 用法：php artisan erp:low-stock-alert [--warehouse=1]
 */
class LowStockAlertCommand extends Command
{
    protected $signature = 'erp:low-stock-alert {--warehouse= : 仅检查指定仓库}';

    protected $description = '扫描低于安全库存的物料并汇总通知仓储角色';

    public function handle(NotificationService $notifications): int
    {
        if (! config('monitoring.low_stock.enabled', true)) {
            $this->line('低库存预警已在配置中关闭');

            return self::SUCCESS;
        }

        $query = DB::table('inventory_balances')
            ->join('items', 'inventory_balances.item_id', '=', 'items.id')
            ->whereNull('items.deleted_at')
            ->where('items.safety_stock', '>', 0)
            ->selectRaw(
                'items.id as item_id, items.code as item_code, items.name as item_name,'
                . ' items.safety_stock as safety_stock, inventory_balances.warehouse_id as warehouse_id,'
                . ' SUM(inventory_balances.quantity) as quantity'
            )
            ->groupBy('items.id', 'items.code', 'items.name', 'items.safety_stock', 'inventory_balances.warehouse_id')
            ->havingRaw('quantity < items.safety_stock');

        if ($this->option('warehouse')) {
            $query->where('inventory_balances.warehouse_id', (int) $this->option('warehouse'));
        }

        $items = $query->get();

        if ($items->isEmpty()) {
            $this->info('未发现低于安全库存的物料');

            return self::SUCCESS;
        }

        $rows = $items->map(fn ($row) => [
            $row->item_code,
            $row->item_name,
            (float) $row->quantity,
            (float) $row->safety_stock,
            'W#' . $row->warehouse_id,
        ])->all();

        $this->table(['物料编码', '物料名称', '当前库存', '安全库存', '仓库'], $rows);

        $body = "以下 {$items->count()} 个物料的库存已低于安全库存，请及时补货：\n\n";
        foreach ($items->take(30) as $row) {
            $body .= sprintf(
                "· %s %s：现有 %.2f，安全库存 %.2f（仓库 #%d）\n",
                $row->item_code,
                $row->item_name,
                (float) $row->quantity,
                (float) $row->safety_stock,
                (int) $row->warehouse_id
            );
        }
        if ($items->count() > 30) {
            $body .= "\n（仅列出前 30 条，共 {$items->count()} 条）";
        }

        $notifications->sendToRole(
            config('monitoring.low_stock.role', 'warehouse_manager'),
            '低库存预警：' . $items->count() . ' 个物料需补货',
            $body,
            'warning',
            route('app.inventory.index'),
            ['count' => $items->count()]
        );

        $this->info('已发送低库存预警通知');

        return self::SUCCESS;
    }
}
