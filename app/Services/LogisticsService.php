<?php

namespace App\Services;

use App\Models\Logistics\ExpressProvider;
use App\Models\Logistics\LogisticsShipment;
use App\Models\Logistics\LogisticsTrace;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * 物流查询服务
 *
 * 三档回退，保证任何环境都能用：
 *  1) 已配置密钥 → 调用聚合通道的正式 API（快递100 / 快递鸟 / 菜鸟）
 *  2) 未配置密钥 → 调用快递100 的免费公共查询接口（无需鉴权，尽力而为）
 *  3) 网络不可用 → 返回演示轨迹，并在返回体里标记 is_demo=true，界面上明确提示
 */
class LogisticsService
{
    /**
     * 查询并入库
     *
     * @return array{success:bool, source:string, is_demo:bool, status:string, traces:array, message:?string, shipment:?LogisticsShipment}
     */
    public function query(string $carrierKey, string $trackingNo, bool $persist = true): array
    {
        $trackingNo = trim($trackingNo);
        $carrier = ExpressProvider::carrier()->where('key', $carrierKey)->first();
        $carrierCode = $carrier?->code ?: $carrierKey;

        $raw = $this->fetch($carrierCode, $trackingNo);

        $shipment = null;
        if ($persist) {
            $previousStatus = null;
            $shipment = LogisticsShipment::firstOrNew(['tracking_no' => $trackingNo]);
            if ($shipment->exists) {
                $previousStatus = $shipment->status;
            } else {
                $shipment->carrier_key = $carrierKey;
                $shipment->carrier_name = $carrier?->name;
                $shipment->created_by = auth()->id();
            }
            $shipment->status = $raw['status'];
            $shipment->carrier_name = $shipment->carrier_name ?: $carrier?->name;
            $shipment->last_traced_at = now();
            if ($raw['status'] === LogisticsShipment::STATUS_SIGNED) {
                $shipment->signed_at = $shipment->signed_at ?: now();
            }
            $shipment->save();

            // 覆盖式写入轨迹（幂等：先删后插）
            $shipment->traces()->delete();
            foreach ($raw['traces'] as $t) {
                LogisticsTrace::create([
                    'shipment_id' => $shipment->id,
                    'traced_at' => $t['time'] ?: null,
                    'status' => $t['status'] ?? null,
                    'description' => $t['description'] ?? null,
                    'location' => $t['location'] ?? null,
                ]);
            }

            if ($previousStatus && $previousStatus !== $shipment->status) {
                $this->notifyStatusChanged($shipment, $previousStatus);
            }
        }

        return [
            'success' => $raw['success'],
            'source' => $raw['source'],
            'is_demo' => $raw['is_demo'],
            'status' => $raw['status'],
            'traces' => $raw['traces'],
            'message' => $raw['message'] ?? null,
            'shipment' => $shipment,
        ];
    }

    /**
     * 抓取轨迹（正式 API → 公共接口 → 演示数据）
     */
    protected function fetch(string $carrierCode, string $trackingNo): array
    {
        $channel = ExpressProvider::defaultChannel();

        if ($channel && $channel->hasRealKey() && $channel->key === 'kuaidi100') {
            $result = $this->queryKuaidi100Signed($channel, $carrierCode, $trackingNo);
            if ($result['success']) {
                return $result;
            }
        }

        // 免费公共查询
        $public = $this->queryPublic($carrierCode, $trackingNo);
        if ($public['success']) {
            return $public;
        }

        return $this->demoTraces($carrierCode, $trackingNo);
    }

    /**
     * 快递100 正式签名接口
     */
    protected function queryKuaidi100Signed(ExpressProvider $channel, string $carrierCode, string $trackingNo): array
    {
        try {
            $param = json_encode([
                'com' => $carrierCode,
                'num' => $trackingNo,
                'resultv2' => '4',
            ], JSON_UNESCAPED_UNICODE);

            $sign = strtoupper(md5($param . $channel->app_key . $channel->app_secret));

            $resp = Http::asForm()->timeout(15)->post(rtrim((string) $channel->api_base, '/') . '/poll/query.do', [
                'customer' => $channel->app_secret,
                'sign' => $sign,
                'param' => $param,
            ]);

            if (! $resp->successful()) {
                return ['success' => false, 'message' => '通道返回 HTTP ' . $resp->status()];
            }

            $json = $resp->json();
            if (empty($json['data'])) {
                return ['success' => false, 'message' => $json['message'] ?? '未查询到轨迹'];
            }

            $traces = array_reverse($json['data']);
            $list = array_map(fn ($t) => [
                'time' => $t['time'] ?? null,
                'status' => $t['status'] ?? null,
                'description' => $t['context'] ?? null,
                'location' => null,
            ], $traces);

            return [
                'success' => true,
                'source' => 'channel',
                'is_demo' => false,
                'status' => $this->mapStatus($json['state'] ?? '', $list),
                'traces' => $list,
            ];
        } catch (\Throwable $e) {
            Log::warning('物流通道查询异常：' . $e->getMessage());

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 快递100 免费公共查询（无需密钥）
     */
    protected function queryPublic(string $carrierCode, string $trackingNo): array
    {
        try {
            $resp = Http::timeout(10)
                ->withHeaders(['Referer' => 'https://www.kuaidi100.com/'])
                ->get('https://www.kuaidi100.com/query', [
                    'type' => $carrierCode,
                    'postid' => $trackingNo,
                    'temp' => '0.1',
                ]);

            if (! $resp->successful()) {
                return ['success' => false, 'message' => '公共查询失败'];
            }

            $json = $resp->json();
            if (empty($json['data'])) {
                return ['success' => false, 'message' => $json['message'] ?? '未查询到轨迹'];
            }

            $traces = array_reverse($json['data']);
            $list = array_map(fn ($t) => [
                'time' => $t['time'] ?? null,
                'status' => $t['status'] ?? null,
                'description' => $t['context'] ?? null,
                'location' => null,
            ], $traces);

            return [
                'success' => true,
                'source' => 'public',
                'is_demo' => false,
                'status' => $this->mapStatus($json['state'] ?? '', $list),
                'traces' => $list,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 演示轨迹：网络不可用或未开通任何通道时使用
     */
    protected function demoTraces(string $carrierCode, string $trackingNo): array
    {
        $carrier = collect(ExpressProvider::carriers())
            ->firstWhere('code', $carrierCode);
        $name = $carrier['name'] ?? '快递公司';

        $base = now()->subDays(2);
        $traces = [
            ['time' => $base->format('Y-m-d H:i:s'), 'status' => '已揽收', 'description' => "【{$name}】已在始发地完成揽收", 'location' => '始发地'],
            ['time' => $base->copy()->addHours(6)->format('Y-m-d H:i:s'), 'status' => '运输中', 'description' => '快件已到达【中转中心】', 'location' => '中转中心'],
            ['time' => $base->copy()->addHours(20)->format('Y-m-d H:i:s'), 'status' => '运输中', 'description' => '快件已发往【目的地城市】', 'location' => '目的地城市'],
            ['time' => $base->copy()->addDays(1)->addHours(4)->format('Y-m-d H:i:s'), 'status' => '派送中', 'description' => '快递员正在派送，请保持电话畅通', 'location' => '目的地网点'],
        ];

        return [
            'success' => true,
            'source' => 'demo',
            'is_demo' => true,
            'status' => LogisticsShipment::STATUS_DELIVERING,
            'traces' => $traces,
            'message' => '当前为演示轨迹（未配置物流通道密钥或网络不可用）',
        ];
    }

    /**
     * 通道状态码 → 本系统状态
     */
    protected function mapStatus(string $state, array $traces): string
    {
        $latest = strtolower((string) ($traces[count($traces) - 1]['status'] ?? ''));

        if (str_contains($latest, '签收') || str_contains($latest, '已取')) {
            return LogisticsShipment::STATUS_SIGNED;
        }
        if (str_contains($latest, '派送') || str_contains($latest, '派件')) {
            return LogisticsShipment::STATUS_DELIVERING;
        }
        if (str_contains($latest, '退回') || str_contains($latest, '退回中')) {
            return LogisticsShipment::STATUS_RETURNING;
        }
        if (str_contains($latest, '异常') || str_contains($latest, '问题件')) {
            return LogisticsShipment::STATUS_EXCEPTION;
        }
        if (str_contains($latest, '运输') || str_contains($latest, '到达') || str_contains($latest, '发出')) {
            return LogisticsShipment::STATUS_IN_TRANSIT;
        }

        return match ($state) {
            '0' => LogisticsShipment::STATUS_IN_TRANSIT,
            '1' => LogisticsShipment::STATUS_DELIVERING,
            '2' => LogisticsShipment::STATUS_EXCEPTION,
            '3' => LogisticsShipment::STATUS_SIGNED,
            '5' => LogisticsShipment::STATUS_DELIVERING,
            '6' => LogisticsShipment::STATUS_RETURNING,
            default => LogisticsShipment::STATUS_CREATED,
        };
    }

    /**
     * 状态变化 → 站内消息通知（消息系统集成点）
     */
    public function notifyStatusChanged(LogisticsShipment $shipment, string $from): void
    {
        if (! $shipment->notify_enabled) {
            return;
        }

        $notify = app(NotificationService::class);
        $label = $shipment->statusLabel();
        $title = '物流状态更新：' . $shipment->tracking_no;
        $body = sprintf(
            '%s 运单 %s 由「%s」变更为「%s」',
            $shipment->carrier_name ?: '快递',
            $shipment->tracking_no,
            LogisticsShipment::STATUS_LABELS[$from] ?? $from,
            $label
        );
        $link = route('app.inventory.logistics') . '?tracking_no=' . urlencode($shipment->tracking_no);

        if ($shipment->created_by) {
            $notify->send((int) $shipment->created_by, $title, $body, 'logistics', $link, [
                'shipment_id' => $shipment->id,
                'from' => $from,
                'to' => $shipment->status,
            ]);
        }

        // 异常件/签收/退回同时提醒仓储
        if (in_array($shipment->status, [
            LogisticsShipment::STATUS_SIGNED,
            LogisticsShipment::STATUS_EXCEPTION,
            LogisticsShipment::STATUS_RETURNING,
        ], true)) {
            $notify->sendToRole('warehouse_manager', $title, $body, 'logistics', $link, [
                'shipment_id' => $shipment->id,
                'to' => $shipment->status,
            ]);
        }
    }

    /**
     * 测试通道连通性
     */
    public function testChannel(ExpressProvider $channel): array
    {
        if (! $channel->hasRealKey()) {
            return ['ok' => false, 'message' => '尚未填写 AppKey / AppSecret，无法测试'];
        }

        try {
            $resp = Http::timeout(10)->get('https://www.kuaidi100.com/autonumber/auto', [
                'num' => '780098068058',
                'key' => $channel->app_key,
            ]);

            return $resp->successful()
                ? ['ok' => true, 'message' => '通道可达（HTTP ' . $resp->status() . '）']
                : ['ok' => false, 'message' => '通道返回 HTTP ' . $resp->status()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => '异常：' . $e->getMessage()];
        }
    }
}
