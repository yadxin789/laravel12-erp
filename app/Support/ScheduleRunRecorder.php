<?php

namespace App\Support;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Support\Facades\Cache;

/**
 * 定时任务执行台账
 *
 * Laravel 的 Schedule 只负责「到点了就跑」，不会记录跑没跑、耗时多少、退出码是什么。
 * 管理员要看「这个任务到底有没有在正常跑」，必须自己留痕。
 *
 * 事件在 AppServiceProvider 中监听 ScheduledTaskFinished / Failed / Skipped 写入，
 * 落在缓存里（默认保留 7 天）：
 *   erp:schedule:last:{key}  最后一次执行
 *   erp:schedule:log:{key}   最近 10 次执行
 *
 * key 直接由 Laravel 自己的 mutexName() 衍生（expression + command 的哈希），
 * 这样「调度清单」与「执行台账」一定对得上，不会因为两边各自算 key 而错位。
 */
class ScheduleRunRecorder
{
    public const PREFIX_LAST = 'erp:schedule:last:';

    public const PREFIX_LOG = 'erp:schedule:log:';

    /** 台账保留天数 */
    public const TTL_DAYS = 7;

    /** 单任务最多保留的执行记录条数 */
    public const MAX_RUNS = 10;

    /**
     * 任务唯一标识：与 Laravel 的互斥锁键同源于 expression + command
     */
    public static function keyFor(Event $event): string
    {
        try {
            return sha1($event->mutexName());
        } catch (\Throwable) {
            return sha1($event->getExpression() . (string) $event->command);
        }
    }

    /**
     * 记录一次执行结果
     *
     * @param  array<string, mixed>  $payload
     */
    public function record(Event $event, array $payload): void
    {
        $key = self::keyFor($event);

        $entry = array_merge([
            'ran_at' => now()->toIso8601String(),
            'status' => 'unknown',
            'duration_ms' => null,
            'exit_code' => null,
            'exception' => null,
        ], $payload);

        Cache::put(self::PREFIX_LAST . $key, $entry, now()->addDays(self::TTL_DAYS));

        $log = Cache::get(self::PREFIX_LOG . $key, []);
        array_unshift($log, $entry);

        Cache::put(self::PREFIX_LOG . $key, array_slice($log, 0, self::MAX_RUNS), now()->addDays(self::TTL_DAYS));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function last(Event $event): ?array
    {
        return Cache::get(self::PREFIX_LAST . self::keyFor($event));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function recent(Event $event, int $limit = 5): array
    {
        return array_slice(Cache::get(self::PREFIX_LOG . self::keyFor($event), []), 0, $limit);
    }
}
