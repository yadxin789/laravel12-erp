<?php

namespace App\Services;

use App\Models\Inventory\InventoryBalance;
use App\Models\MasterData\Item;
use App\Models\MasterData\Customer;
use App\Models\MasterData\Supplier;
use App\Models\Sales\SalesOrder;
use App\Models\Purchasing\PurchaseOrder;
use App\Models\Production\ProductionOrder;
use Illuminate\Support\Facades\DB;

/**
 * AI 工具注册中心
 * 提供 AI Agent 可调用的 ERP 内部数据查询能力
 */
class AiToolService
{
    /**
     * 返回所有可用工具的定义（供 AI function calling 使用）
     */
    public function definitions(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_item',
                    'description' => '根据物料编码、名称或规格搜索物料信息',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'keyword' => ['type' => 'string', 'description' => '物料编码、名称关键词或规格'],
                        ],
                        'required' => ['keyword'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_inventory',
                    'description' => '查询物料在各仓库的库存数量、批次信息',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'item_code' => ['type' => 'string', 'description' => '物料编码'],
                            'warehouse_id' => ['type' => 'integer', 'description' => '仓库ID（可选，不填查全部仓库）'],
                        ],
                        'required' => ['item_code'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_low_stock_items',
                    'description' => '查询库存低于安全库存的物料列表（缺货预警）',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_sales_orders',
                    'description' => '查询销售订单列表，可按客户名、状态筛选',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'customer_name' => ['type' => 'string', 'description' => '客户名称关键词（可选）'],
                            'status' => ['type' => 'string', 'description' => '订单状态：draft/submitted/approved/completed/closed/cancelled'],
                            'limit' => ['type' => 'integer', 'description' => '返回条数，默认10'],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_purchase_orders',
                    'description' => '查询采购订单列表，可按供应商名、状态筛选',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'supplier_name' => ['type' => 'string', 'description' => '供应商名称关键词（可选）'],
                            'status' => ['type' => 'string', 'description' => '订单状态'],
                            'limit' => ['type' => 'integer', 'description' => '返回条数，默认10'],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_production_orders',
                    'description' => '查询生产工单列表及状态',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'status' => ['type' => 'string', 'description' => '工单状态：draft/ready/in_progress/completed/closed'],
                            'limit' => ['type' => 'integer', 'description' => '返回条数，默认10'],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'list_customers',
                    'description' => '搜索客户列表',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'keyword' => ['type' => 'string', 'description' => '客户名称关键词'],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'list_suppliers',
                    'description' => '搜索供应商列表',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'keyword' => ['type' => 'string', 'description' => '供应商名称关键词'],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_dashboard_summary',
                    'description' => '获取经营概览：今日销售订单数、待审批采购单、在制工单、低库存物料数',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [],
                    ],
                ],
            ],
        ];
    }

    /**
     * 执行工具调用
     */
    public function call(string $name, array $args): string
    {
        return match ($name) {
            'search_item' => $this->searchItem($args['keyword'] ?? ''),
            'get_inventory' => $this->getInventory($args['item_code'] ?? '', $args['warehouse_id'] ?? null),
            'get_low_stock_items' => $this->getLowStockItems(),
            'get_sales_orders' => $this->getSalesOrders($args['customer_name'] ?? null, $args['status'] ?? null, $args['limit'] ?? 10),
            'get_purchase_orders' => $this->getPurchaseOrders($args['supplier_name'] ?? null, $args['status'] ?? null, $args['limit'] ?? 10),
            'get_production_orders' => $this->getProductionOrders($args['status'] ?? null, $args['limit'] ?? 10),
            'list_customers' => $this->listEntities(Customer::class, $args['keyword'] ?? ''),
            'list_suppliers' => $this->listEntities(Supplier::class, $args['keyword'] ?? ''),
            'get_dashboard_summary' => $this->getDashboardSummary(),
            default => "未知工具: {$name}",
        };
    }

    // ========== 工具实现 ==========

    protected function searchItem(string $keyword): string
    {
        if (empty($keyword)) return '请提供物料关键词';

        $items = Item::where('active', true)
            ->where(function ($q) use ($keyword) {
                $q->where('code', 'like', "%{$keyword}%")
                  ->orWhere('name', 'like', "%{$keyword}%")
                  ->orWhere('spec', 'like', "%{$keyword}%");
            })
            ->with('unit')
            ->limit(10)
            ->get();

        if ($items->isEmpty()) return "未找到匹配「{$keyword}」的物料";

        $result = "找到 {$items->count()} 个物料：\n";
        foreach ($items as $item) {
            $unitName = $item->unit?->name ?? '—';
            $spec = $item->spec ?: '—';
            $result .= "- [{$item->code}] {$item->name}（规格：{$spec}，单位：{$unitName}）\n";
        }
        return $result;
    }

    protected function getInventory(string $itemCode, ?int $warehouseId = null): string
    {
        $item = Item::where('code', $itemCode)->orWhere('barcode', $itemCode)->first();
        if (!$item) return "物料编码 {$itemCode} 不存在";

        $query = InventoryBalance::where('item_id', $item->id);
        if ($warehouseId) $query->where('warehouse_id', $warehouseId);
        $balances = $query->with('warehouse')->get();

        if ($balances->isEmpty()) return "物料 [{$item->code}] {$item->name} 当前无库存记录";

        $result = "物料 [{$item->code}] {$item->name} 库存：\n";
        foreach ($balances as $b) {
            $whName = $b->warehouse?->name ?? '未知仓库';
            $batch = $b->batch_number ?: '无';
            $result .= "- {$whName}：可用 {$b->available_quantity}，在库 {$b->quantity}，批次 {$batch}\n";
        }
        return $result;
    }

    protected function getLowStockItems(): string
    {
        $items = Item::where('active', true)
            ->whereColumn('safety_stock', '>', 0)
            ->withSum(['balances as total_qty' => function ($q) {
                $q->select(DB::raw('COALESCE(SUM(quantity),0)'));
            }], 'quantity')
            ->havingRaw('total_qty < safety_stock')
            ->limit(20)
            ->get();

        if ($items->isEmpty()) return "当前没有低于安全库存的物料";

        $result = "⚠️ 低库存预警（{$items->count()} 项）：\n";
        foreach ($items as $item) {
            $result .= "- [{$item->code}] {$item->name}：当前 {$item->total_qty}，安全库存 {$item->safety_stock}\n";
        }
        return $result;
    }

    protected function getSalesOrders(?string $customerName, ?string $status, int $limit): string
    {
        $query = SalesOrder::with('customer')->latest('order_date');
        if ($customerName) $query->whereHas('customer', fn($q) => $q->where('name', 'like', "%{$customerName}%"));
        if ($status) $query->where('status', $status);
        $orders = $query->limit($limit)->get();

        if ($orders->isEmpty()) return '无符合条件的销售订单';

        $result = "销售订单（{$orders->count()} 条）：\n";
        foreach ($orders as $o) {
            $custName = $o->customer?->name ?? '—';
            $result .= "- {$o->order_no} | {$custName} | 金额：¥{$o->total_amount} | 状态：{$o->status} | 日期：{$o->order_date}\n";
        }
        return $result;
    }

    protected function getPurchaseOrders(?string $supplierName, ?string $status, int $limit): string
    {
        $query = PurchaseOrder::with('supplier')->latest('order_date');
        if ($supplierName) $query->whereHas('supplier', fn($q) => $q->where('name', 'like', "%{$supplierName}%"));
        if ($status) $query->where('status', $status);
        $orders = $query->limit($limit)->get();

        if ($orders->isEmpty()) return '无符合条件的采购订单';

        $result = "采购订单（{$orders->count()} 条）：\n";
        foreach ($orders as $o) {
            $supName = $o->supplier?->name ?? '—';
            $result .= "- {$o->order_no} | {$supName} | 金额：¥{$o->total_amount} | 状态：{$o->status} | 日期：{$o->order_date}\n";
        }
        return $result;
    }

    protected function getProductionOrders(?string $status, int $limit): string
    {
        $query = ProductionOrder::with('item')->latest();
        if ($status) $query->where('status', $status);
        $orders = $query->limit($limit)->get();

        if ($orders->isEmpty()) return '无符合条件的生产工单';

        $result = "生产工单（{$orders->count()} 条）：\n";
        foreach ($orders as $o) {
            $itemName = $o->item?->name ?? '—';
            $result .= "- {$o->order_no} | {$itemName} | 进度：{$o->completed_quantity}/{$o->quantity} | 状态：{$o->status}\n";
        }
        return $result;
    }

    protected function listEntities(string $model, string $keyword): string
    {
        $query = $model::query();
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('code', 'like', "%{$keyword}%");
            });
        }
        $entities = $query->limit(10)->get(['id', 'code', 'name']);

        if ($entities->isEmpty()) return "未找到「{$keyword}」相关记录";

        $result = "";
        foreach ($entities as $e) {
            $result .= "- [{$e->code}] {$e->name}\n";
        }
        return $result;
    }

    protected function getDashboardSummary(): string
    {
        $today = now()->toDateString();
        return "经营概览：\n"
            . "- 今日销售订单：" . SalesOrder::whereDate('order_date', $today)->count() . " 笔\n"
            . "- 待审批销售：" . SalesOrder::where('status', 'submitted')->count() . " 笔\n"
            . "- 待审批采购：" . PurchaseOrder::where('status', 'submitted')->count() . " 笔\n"
            . "- 在制工单：" . ProductionOrder::where('status', 'in_progress')->count() . " 个\n"
            . "- 低库存预警：" . Item::where('active', true)->whereColumn('safety_stock', '>', 0)->count() . " 个物料设了安全库存\n"
            . "- 客户总数：" . Customer::count() . " 家\n"
            . "- 供应商总数：" . Supplier::count() . " 家";
    }
}
