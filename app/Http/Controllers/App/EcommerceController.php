<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\EcommerceOrder;
use Illuminate\Http\Request;

/**
 * 电商订单模块：多平台订单归集 → 同步 → 发货 → 完成
 */
class EcommerceController extends Controller
{
    public function index(Request $request)
    {
        $query = EcommerceOrder::query();

        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('keyword')) {
            $kw = $request->keyword;
            $query->where(function ($q) use ($kw) {
                $q->where('platform_order_no', 'like', "%{$kw}%")
                    ->orWhere('buyer_name', 'like', "%{$kw}%")
                    ->orWhere('buyer_phone', 'like', "%{$kw}%");
            });
        }

        $orders = $query->latest()->paginate(20)->appends($request->query());

        $stats = [
            'total' => EcommerceOrder::count(),
            'pending' => EcommerceOrder::where('status', 'pending')->count(),
            'shipped' => EcommerceOrder::whereIn('status', ['shipped'])->count(),
            'amount' => (float) EcommerceOrder::whereNotIn('status', ['cancelled'])->sum('total_amount'),
        ];

        return view('app.ecommerce.index', [
            'orders' => $orders,
            'stats' => $stats,
            'platforms' => EcommerceOrder::PLATFORMS,
            'statusLabels' => EcommerceOrder::STATUS,
        ]);
    }

    public function create()
    {
        return view('app.ecommerce.create', [
            'platforms' => EcommerceOrder::PLATFORMS,
            'statusLabels' => EcommerceOrder::STATUS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'platform' => 'required|in:' . implode(',', array_keys(EcommerceOrder::PLATFORMS)),
            'platform_order_no' => 'required|string|max:100',
            'buyer_name' => 'nullable|string|max:100',
            'buyer_phone' => 'nullable|string|max:50',
            'shipping_address' => 'nullable|string',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:' . implode(',', array_keys(EcommerceOrder::STATUS)),
            'items_text' => 'nullable|string',
        ], [
            'platform_order_no.required' => '请填写平台订单号',
            'total_amount.required' => '请填写订单金额',
        ]);

        $data['items'] = $this->parseItems($request->items_text);
        unset($data['items_text']);

        EcommerceOrder::create($data);

        return redirect()->route('app.ecommerce.index')->with('success', '电商订单已创建');
    }

    public function show(EcommerceOrder $order)
    {
        return view('app.ecommerce.show', [
            'order' => $order,
            'platforms' => EcommerceOrder::PLATFORMS,
            'statusLabels' => EcommerceOrder::STATUS,
        ]);
    }

    public function edit(EcommerceOrder $order)
    {
        return view('app.ecommerce.edit', [
            'order' => $order,
            'platforms' => EcommerceOrder::PLATFORMS,
            'statusLabels' => EcommerceOrder::STATUS,
        ]);
    }

    public function update(Request $request, EcommerceOrder $order)
    {
        $data = $request->validate([
            'platform' => 'required|in:' . implode(',', array_keys(EcommerceOrder::PLATFORMS)),
            'platform_order_no' => 'required|string|max:100',
            'buyer_name' => 'nullable|string|max:100',
            'buyer_phone' => 'nullable|string|max:50',
            'shipping_address' => 'nullable|string',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:' . implode(',', array_keys(EcommerceOrder::STATUS)),
            'items_text' => 'nullable|string',
        ]);

        $data['items'] = $this->parseItems($request->items_text);
        unset($data['items_text']);

        $order->update($data);

        return redirect()->route('app.ecommerce.index')->with('success', '电商订单已更新');
    }

    public function changeStatus(Request $request, EcommerceOrder $order)
    {
        $data = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(EcommerceOrder::STATUS)),
        ]);

        $order->update(['status' => $data['status']]);

        return back()->with('success', '订单状态已更新为「' . EcommerceOrder::STATUS[$data['status']] . '」');
    }

    /**
     * 平台订单同步：把「待处理」订单拉取为「已同步」
     * （真实环境在此调用各平台开放 API；此处按本地待处理订单落库，演示闭环）
     */
    public function sync(Request $request)
    {
        $platform = $request->input('platform');

        $query = EcommerceOrder::where('status', 'pending');
        if ($platform) {
            $query->where('platform', $platform);
        }

        $count = $query->count();
        $query->update(['status' => 'synced', 'synced_at' => now()]);

        return back()->with('success', $count > 0
            ? "同步完成，本次同步 {$count} 条订单"
            : '没有待同步的订单');
    }

    public function destroy(EcommerceOrder $order)
    {
        $order->delete();

        return redirect()->route('app.ecommerce.index')->with('success', '电商订单已删除');
    }

    /**
     * 明细文本解析：每行「物料编码 数量 单价」
     */
    private function parseItems(?string $text): array
    {
        if (empty($text)) {
            return [];
        }

        $items = [];
        foreach (preg_split('/\r\n|\r|\n/', $text) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = preg_split('/\s+/', $line);
            $items[] = [
                'code' => $parts[0] ?? '',
                'qty' => isset($parts[1]) ? (float) $parts[1] : 1,
                'price' => isset($parts[2]) ? (float) $parts[2] : 0,
            ];
        }

        return $items;
    }
}
