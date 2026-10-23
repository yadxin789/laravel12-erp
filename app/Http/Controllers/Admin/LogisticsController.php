<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Logistics\ExpressProvider;
use App\Models\Logistics\LogisticsShipment;
use App\Services\LogisticsService;
use Illuminate\Http\Request;

/**
 * 管理员：国内物流快递 — 开通引导 + 通道/快递公司设置 + 运单管理
 */
class LogisticsController extends Controller
{
    /**
     * 开通引导页（三步：选通道 → 申请密钥 → 粘贴测试）
     */
    public function guide()
    {
        ExpressProvider::syncFromCatalog();

        $channels = collect(ExpressProvider::channels())
            ->map(fn ($c, $key) => array_merge($c, [
                'key' => $key,
                'row' => ExpressProvider::channel()->where('key', $key)->first(),
            ]));

        $configured = ExpressProvider::channel()->where('enabled', true)
            ->whereNotNull('app_key')->exists();

        return view('admin.logistics.guide', compact('channels', 'configured'));
    }

    /**
     * 设置页
     */
    public function settings()
    {
        ExpressProvider::syncFromCatalog();

        $channelRows = ExpressProvider::channel()->orderBy('sort_order')->get()->keyBy('key');
        $carrierRows = ExpressProvider::carrier()->orderBy('sort_order')->get()->keyBy('key');
        $default = ExpressProvider::defaultChannel();

        return view('admin.logistics.settings', [
            'channels' => ExpressProvider::channels(),
            'carriers' => ExpressProvider::carriers(),
            'channelRows' => $channelRows,
            'carrierRows' => $carrierRows,
            'default' => $default,
            'stats' => [
                'shipments' => LogisticsShipment::count(),
                'in_transit' => LogisticsShipment::whereIn('status', [
                    LogisticsShipment::STATUS_IN_TRANSIT,
                    LogisticsShipment::STATUS_DELIVERING,
                ])->count(),
                'signed' => LogisticsShipment::where('status', LogisticsShipment::STATUS_SIGNED)->count(),
                'exception' => LogisticsShipment::where('status', LogisticsShipment::STATUS_EXCEPTION)->count(),
            ],
        ]);
    }

    /**
     * 保存查询通道密钥 + 快递公司开关
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'app_key' => 'nullable|string|max:500',
            'app_secret' => 'nullable|string|max:500',
            'api_base' => 'nullable|string|max:500',
            'channel' => 'nullable|string|max:40',
            'carriers' => 'nullable|array',
            'carriers.*' => 'string|max:40',
            'monthly_quota' => 'nullable|integer|min:0|max:1000000',
            'notify_enabled' => 'boolean',
            'source' => 'nullable|string|max:20',
        ]);

        // 保存通道
        if (! empty($validated['channel'])) {
            $row = ExpressProvider::channel()->where('key', $validated['channel'])->firstOrFail();
            $data = [];
            if (! empty($validated['app_key'])) {
                $data['app_key'] = trim($validated['app_key']);
                $data['enabled'] = true;
            }
            if (! empty($validated['app_secret'])) {
                $data['app_secret'] = trim($validated['app_secret']);
            }
            if (! empty($validated['api_base'])) {
                $data['api_base'] = trim($validated['api_base']);
            }
            if (! empty($validated['monthly_quota'])) {
                $data['monthly_quota'] = (int) $validated['monthly_quota'];
            }
            if ($data) {
                $row->update($data);
                if ($row->enabled && ! ExpressProvider::channel()->where('is_default', true)->exists()) {
                    $row->markAsDefault();
                }
            }
        }

        // 快递公司启用清单
        if ($request->has('carriers')) {
            $enabled = $validated['carriers'] ?? [];
            foreach (ExpressProvider::carrier()->get() as $carrier) {
                $carrier->update(['enabled' => in_array($carrier->key, $enabled, true)]);
            }
        }

        return back()->with('success', '物流设置已保存');
    }

    public function saveProvider(Request $request, string $provider)
    {
        $row = ExpressProvider::where('key', $provider)->firstOrFail();

        $validated = $request->validate([
            'app_key' => 'nullable|string|max:500',
            'app_secret' => 'nullable|string|max:500',
            'api_base' => 'nullable|string|max:500',
            'note' => 'nullable|string|max:500',
            'monthly_quota' => 'nullable|integer|min:0|max:1000000',
        ]);

        $data = [];
        foreach (['app_key', 'app_secret', 'api_base', 'note'] as $field) {
            if (! empty($validated[$field])) {
                $data[$field] = trim($validated[$field]);
            }
        }
        if (! empty($validated['monthly_quota'])) {
            $data['monthly_quota'] = (int) $validated['monthly_quota'];
        }
        if (! empty($data['app_key'])) {
            $data['enabled'] = true;
        }

        $row->update($data);

        return back()->with('success', $row->name . ' 配置已保存');
    }

    public function toggleProvider(string $provider)
    {
        $row = ExpressProvider::where('key', $provider)->firstOrFail();
        $row->update(['enabled' => ! $row->enabled]);

        return back()->with('success', $row->name . ($row->enabled ? ' 已启用' : ' 已停用'));
    }

    public function setDefaultProvider(string $provider)
    {
        $row = ExpressProvider::where('key', $provider)->firstOrFail();
        $row->markAsDefault();

        return back()->with('success', '默认查询通道已设为：' . $row->name);
    }

    public function testProvider(string $provider, LogisticsService $logistics)
    {
        $row = ExpressProvider::where('key', $provider)->firstOrFail();
        $result = $row->type === 'channel'
            ? $logistics->testChannel($row)
            : ['ok' => false, 'message' => '快递公司无需测试，只需启用即可用于查询'];

        return back()->with($result['ok'] ? 'success' : 'error', $row->name . '：' . $result['message']);
    }

    public function syncCatalog()
    {
        ExpressProvider::syncFromCatalog();

        return back()->with('success', '已按目录补齐通道与快递公司');
    }

    /**
     * 运单列表（管理员）
     */
    public function shipments(Request $request)
    {
        $query = LogisticsShipment::with('creator')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('keyword')) {
            $query->where('tracking_no', 'like', '%' . $request->input('keyword') . '%');
        }

        return view('admin.logistics.shipments', [
            'shipments' => $query->paginate(20)->withQueryString(),
        ]);
    }

    /**
     * 手动刷新某个运单的轨迹（会触发状态变化通知）
     */
    public function trace(LogisticsShipment $shipment, LogisticsService $logistics)
    {
        $result = $logistics->query(
            (string) ($shipment->carrier_key ?? ''),
            (string) $shipment->tracking_no
        );

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $shipment->tracking_no . '：' . ($result['message'] ?: '轨迹已更新，状态 ' . LogisticsShipment::STATUS_LABELS[$result['status']] ?? $result['status'])
        );
    }
}
