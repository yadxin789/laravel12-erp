<?php

namespace App\Services;

use App\Models\System\SystemAlert;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * 统一告警服务
 *
 * 语义对齐 Prometheus Alertmanager：
 *  - fire()    触发/累加告警，同 fingerprint 不重复刷屏（冷却 + 升级）
 *  - resolve() 故障恢复，主动发一次「已恢复」通知
 *
 * 通道：站内消息（NotificationService）/ 邮件 / 企业微信·钉钉·飞书机器人。
 */
class AlertService
{
    public function __construct(protected NotificationService $notifications)
    {
    }

    /**
     * 触发告警
     *
     * @param string $source      告警来源：queue / database / disk / backup / stock / logistics
     * @param string $fingerprint 同类告警唯一指纹（同一问题必须稳定不变）
     * @param string $level       critical / warning / info
     */
    public function fire(
        string $source,
        string $fingerprint,
        string $level,
        string $title,
        string $message,
        array $context = []
    ): SystemAlert {
        $alert = SystemAlert::firstOrNew(['fingerprint' => $fingerprint]);

        $isNew = ! $alert->exists;
        $wasResolved = $alert->exists && $alert->status === SystemAlert::STATUS_RESOLVED;

        $alert->fill([
            'source' => $source,
            'level' => $level,
            'title' => $title,
            'message' => $message,
            'context' => $context ?: null,
            'status' => SystemAlert::STATUS_FIRING,
            'resolved_at' => null,
        ]);

        $now = now();
        if ($isNew || $wasResolved) {
            $alert->occurrences = 1;
            $alert->first_triggered_at = $now;
        } else {
            $alert->occurrences = ($alert->occurrences ?? 0) + 1;
        }
        $alert->last_triggered_at = $now;
        $alert->save();

        if (! config('monitoring.enabled', true)) {
            return $alert;
        }

        if ($this->shouldNotify($alert, $isNew || $wasResolved)) {
            $this->notify($alert, $isNew || $wasResolved);
            $alert->notify_count = ($alert->notify_count ?? 0) + 1;
            $alert->last_notified_at = $now;
            $alert->save();
        }

        return $alert;
    }

    /**
     * 恢复告警（对应指标回到正常区间）
     */
    public function resolve(string $fingerprint, ?string $message = null): void
    {
        $alert = SystemAlert::where('fingerprint', $fingerprint)->first();

        if (! $alert || $alert->status === SystemAlert::STATUS_RESOLVED) {
            return;
        }

        $alert->update([
            'status' => SystemAlert::STATUS_RESOLVED,
            'resolved_at' => now(),
        ]);

        if (! config('monitoring.enabled', true)) {
            return;
        }

        $this->notify(
            $alert,
            true,
            '✅ 已恢复：' . $alert->title,
            ($message ?: $alert->message) . "\n\n持续时间：" . $this->durationForHumans($alert)
        );
    }

    /**
     * 批量恢复：本轮检查中未再出现的 fingerprint 视为已恢复
     *
     * @param array  $activeFingerprints 本轮仍然 firing 的指纹
     * @param string $source             只处理该来源，避免误恢复其它来源告警
     */
    public function resolveMissing(string $source, array $activeFingerprints): int
    {
        $stale = SystemAlert::where('source', $source)
            ->where('status', SystemAlert::STATUS_FIRING)
            ->whereNotIn('fingerprint', $activeFingerprints ?: ['__none__'])
            ->get();

        foreach ($stale as $alert) {
            $this->resolve($alert->fingerprint, '健康检查未再检出该问题，判定为已恢复。');
        }

        return $stale->count();
    }

    /**
     * 当前处于 firing 状态的告警
     */
    public function firing()
    {
        return SystemAlert::where('status', SystemAlert::STATUS_FIRING)
            ->orderByRaw("FIELD(level, 'critical', 'warning', 'info')")
            ->orderByDesc('last_triggered_at')
            ->get();
    }

    /**
     * 是否应当发送通知（冷却 / 升级策略）
     */
    protected function shouldNotify(SystemAlert $alert, bool $fresh): bool
    {
        if ($fresh) {
            return true;
        }

        $cooldown = (int) config('monitoring.cooldown_minutes', 30);
        $lastNotified = $alert->last_notified_at;

        if (! $lastNotified || $lastNotified->diffInMinutes(now()) >= $cooldown) {
            return true;
        }

        // 升级：持续触发次数达到阈值后强制再发一次，避免被冷却长期压制
        $escalateAfter = (int) config('monitoring.escalation_after', 10);

        return ($alert->occurrences % max($escalateAfter, 1)) === 0;
    }

    /**
     * 按配置把告警投递到各通道
     */
    protected function notify(SystemAlert $alert, bool $fresh = false, ?string $title = null, ?string $body = null): void
    {
        $title = $title ?: $this->alertTitle($alert, $fresh);
        $body = $body ?: $this->alertBody($alert);

        foreach (['database', 'mail', 'webhook'] as $channel) {
            try {
                match ($channel) {
                    'database' => $this->viaDatabase($alert, $title, $body),
                    'mail' => $this->viaMail($title, $body),
                    'webhook' => $this->viaWebhook($alert, $title, $body),
                };
            } catch (\Throwable $e) {
                // 单一通道失败不能影响其它通道与整体检查
                Log::error("告警通道 {$channel} 发送失败", [
                    'fingerprint' => $alert->fingerprint,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function alertTitle(SystemAlert $alert, bool $fresh): string
    {
        $prefix = match ($alert->level) {
            SystemAlert::LEVEL_CRITICAL => '🚨 严重',
            SystemAlert::LEVEL_WARNING => '⚠️ 警告',
            default => 'ℹ️ 提示',
        };

        $prefix .= $fresh ? '' : "（第 {$alert->occurrences} 次）";

        return $prefix . '：' . $alert->title;
    }

    protected function alertBody(SystemAlert $alert): string
    {
        $lines = [
            $alert->message,
            '',
            '来源：' . $alert->source,
            '级别：' . $alert->levelLabel(),
            '首次触发：' . optional($alert->first_triggered_at)->format('Y-m-d H:i:s'),
            '最近触发：' . optional($alert->last_triggered_at)->format('Y-m-d H:i:s'),
            '累计次数：' . $alert->occurrences,
        ];

        if (! empty($alert->context)) {
            $lines[] = '';
            foreach ($alert->context as $key => $value) {
                $lines[] = "{$key}：" . (is_scalar($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE));
            }
        }

        return implode("\n", $lines);
    }

    protected function durationForHumans(SystemAlert $alert): string
    {
        if (! $alert->first_triggered_at) {
            return '未知';
        }

        return $alert->first_triggered_at->diffForHumans($alert->resolved_at ?? now(), true);
    }

    protected function viaDatabase(SystemAlert $alert, string $title, string $body): void
    {
        if (! config('monitoring.channels.database', true)) {
            return;
        }

        $type = $alert->level === SystemAlert::LEVEL_CRITICAL ? 'danger' : 'warning';

        foreach (config('monitoring.recipients.roles', ['admin']) as $role) {
            $this->notifications->sendToRole(
                trim((string) $role),
                $title,
                $body,
                $type,
                route('admin.system-health'),
                ['fingerprint' => $alert->fingerprint, 'source' => $alert->source]
            );
        }
    }

    protected function viaMail(string $title, string $body): void
    {
        if (! config('monitoring.channels.mail', false)) {
            return;
        }

        $to = config('monitoring.recipients.emails', []);
        if (empty($to)) {
            return;
        }

        Mail::raw($body, function ($message) use ($to, $title) {
            $message->to($to)->subject('[ERP监控] ' . $title);
        });
    }

    protected function viaWebhook(SystemAlert $alert, string $title, string $body): void
    {
        if (! config('monitoring.channels.webhook', false)) {
            return;
        }

        $url = config('monitoring.webhook.url');
        if (! $url) {
            return;
        }

        $payload = match (config('monitoring.webhook.driver', 'wecom')) {
            'dingtalk' => $this->dingtalkPayload($title, $body),
            'feishu' => [
                'msg_type' => 'text',
                'content' => ['text' => $title . "\n" . $body],
            ],
            default => [
                'msgtype' => 'markdown',
                'markdown' => ['content' => "### {$title}\n{$body}"],
            ],
        };

        Http::timeout((int) config('monitoring.webhook.timeout', 10))
            ->post($url, $payload)
            ->throw();
    }

    protected function dingtalkPayload(string $title, string $body): array
    {
        $payload = [
            'msgtype' => 'markdown',
            'markdown' => ['title' => $title, 'text' => "### {$title}\n\n{$body}"],
        ];

        // 钉钉「加签」安全设置
        if ($secret = config('monitoring.webhook.secret')) {
            $timestamp = now()->getTimestampMs();
            $sign = base64_encode(hash_hmac('sha256', $timestamp . "\n" . $secret, $secret, true));
            $payload['timestamp'] = $timestamp;
            $payload['sign'] = urlencode($sign);
        }

        return $payload;
    }
}
