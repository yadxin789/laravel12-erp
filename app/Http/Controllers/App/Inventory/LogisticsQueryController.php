<?php

namespace App\Http\Controllers\App\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Logistics\ExpressProvider;
use App\Models\Logistics\LogisticsShipment;
use App\Services\LogisticsService;
use Illuminate\Http\Request;

/**
 * 仓库模块：物流查询
 */
class LogisticsQueryController extends Controller
{
    /**
     * 查询首页：登记 + 查询 + 最近运单
     */
    public function index(Request $request)
    {
        $carriers = ExpressProvider::carrier()->where('enabled', true)->orderBy('sort_order')->get();
        $channel = ExpressProvider::defaultChannel();

        $result = null;
        if ($request->filled('tracking_no')) {
            $result = app(LogisticsService::class)->query(
                (string) $request->input('carrier', $carriers->first()?->key ?? ''),
                (string) $request->input('tracking_no')
            );
        }

        $shipments = LogisticsShipment::with('creator')->latest()->paginate(15)->withQueryString();

        return view('app.inventory.logistics', compact('carriers', 'channel', 'result', 'shipments'));
    }

    /**
     * 查询（POST，与 GET 共用同一视图）
     */
    public function query(Request $request)
    {
        $validated = $request->validate([
            'carrier' => 'required|string|max:40',
            'tracking_no' => 'required|string|max:64',
        ], [], [
            'carrier' => '快递公司',
            'tracking_no' => '运单号',
        ]);

        return redirect()->route('app.inventory.logistics', $validated);
    }

    /**
     * 登记运单并立即查询一次
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tracking_no' => 'required|string|max:64|unique:logistics_shipments,tracking_no',
            'carrier' => 'required|string|max:40',
            'receiver_name' => 'nullable|string|max:100',
            'receiver_phone' => 'nullable|string|max:30',
            'receiver_address' => 'nullable|string|max:500',
        ], [], [
            'tracking_no' => '运单号',
            'carrier' => '快递公司',
            'receiver_name' => '收件人',
            'receiver_phone' => '联系电话',
            'receiver_address' => '收件地址',
        ]);

        $carrier = ExpressProvider::carrier()->where('key', $validated['carrier'])->first();

        LogisticsShipment::create([
            'tracking_no' => $validated['tracking_no'],
            'carrier_key' => $validated['carrier'],
            'carrier_name' => $carrier?->name,
            'receiver_name' => $validated['receiver_name'] ?? null,
            'receiver_phone' => $validated['receiver_phone'] ?? null,
            'receiver_address' => $validated['receiver_address'] ?? null,
            'created_by' => auth()->id(),
            'status' => LogisticsShipment::STATUS_CREATED,
        ]);

        return redirect()->route('app.inventory.logistics', [
            'carrier' => $validated['carrier'],
            'tracking_no' => $validated['tracking_no'],
        ])->with('success', '运单已登记，已自动查询一次轨迹');
    }

    /**
     * 运单详情
     */
    public function show(LogisticsShipment $shipment)
    {
        $shipment->load('traces');

        return view('app.inventory.logistics-show', ['shipment' => $shipment]);
    }
}
