<?php

namespace App\Console\Commands;

use App\Services\StateTransitionService;
use Illuminate\Console\Command;

/**
 * 状态自动转换定时扫描
 *
 * 把「数量达标 / 到期 / 物料重新齐套」驱动的状态跃迁补算一遍：
 * 报价单到期失效、缺料工单恢复待开工、销售/采购订单收发状态对账、库存预留到期释放。
 *
 * 由 routes/console.php 以 everyFiveSeconds() 调度（亚分钟调度依赖 schedule:run 每分钟被拉起，
 * Laravel 会在该分钟剩余时间内持续轮询到期的任务）。
 *
 * 用法：
 *   php artisan erp:state-transition                 按默认配置扫描全部模块
 *   php artisan erp:state-transition --dry-run       只报告会发生的转换，不写库
 *   php artisan erp:state-transition --module=quotation,sales_order
 *   php artisan erp:state-transition --limit=50
 */
class StateTransitionScanCommand extends Command
{
    protected $signature = 'erp:state-transition
                            {--limit= : 每个模块单次最多处理的记录数，缺省取 monitoring.state_transition.scan_limit}
                            {--module= : 只扫描指定模块，多个用逗号分隔（quotation / production_order / sales_order / purchase_order / inventory_reservation）}
                            {--dry-run : 只输出将要发生的转换，不写库}';

    protected $description = '扫描各模块单据，按既有的状态流转规则自动推进状态';

    public function handle(StateTransitionService $transitions): int
    {
        if (! config('monitoring.state_transition.enabled', true)) {
            $this->line('状态自动转换已在配置中关闭（ERP_STATE_TRANSITION_ENABLED=false）');

            return self::SUCCESS;
        }

        $limit = (int) ($this->option('limit') ?: config('monitoring.state_transition.scan_limit', 200));
        $dryRun = (bool) $this->option('dry-run');
        $modules = $this->normalizeModules($this->option('module'));

        // 命令行未指定时，退回配置项 monitoring.state_transition.modules（留空表示全部模块）
        if (empty($modules)) {
            $modules = $this->normalizeModules(config('monitoring.state_transition.modules', []));
        }

        $started = microtime(true);
        $report = $transitions->scan(max(1, $limit), $modules, $dryRun);
        $elapsed = (int) round((microtime(true) - $started) * 1000);

        $total = collect($report)->sum(fn ($module) => count($module['transitions']));

        if ($total === 0) {
            $this->line(sprintf(
                '无需要自动转换的单据（扫描 %d 个模块，耗时 %d ms）',
                count($report),
                $elapsed
            ));
        } else {
            $rows = [];
            foreach ($report as $transitionsOfModule) {
                foreach ($transitionsOfModule['transitions'] as $transition) {
                    $rows[] = [
                        $transitionsOfModule['name'],
                        '#' . $transition['id'],
                        $transition['from'],
                        $transition['to'],
                        $transition['remark'],
                    ];
                }
            }

            $this->table(['模块', '单据', '原状态', '新状态', '说明'], $rows);
            $this->info(sprintf('共转换 %d 条单据，耗时 %d ms', $total, $elapsed));
        }

        foreach ($report as $module => $transitionsOfModule) {
            if ($transitionsOfModule['error']) {
                $this->warn("[{$module}] 扫描失败：{$transitionsOfModule['error']}");
            }
        }

        if ($dryRun) {
            $this->warn('以上为预演结果（--dry-run），未写入数据库');
        }

        return self::SUCCESS;
    }

    /**
     * 模块参数既支持逗号分隔的字符串，也支持数组（配置项形式）。
     */
    protected function normalizeModules(mixed $modules): array
    {
        $modules = is_array($modules) ? $modules : explode(',', (string) $modules);

        return array_values(array_filter(array_map('trim', $modules)));
    }
}
