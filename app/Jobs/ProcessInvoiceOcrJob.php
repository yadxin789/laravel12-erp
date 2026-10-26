<?php

namespace App\Jobs;

use App\Models\System\AiOcrResult;
use App\Services\AiService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * 发票 OCR 识别
 *
 * 识别结果落 ai_ocr_results 表并通知提交人，调用方通过记录 ID 回查，
 * 解决此前「异步执行但结果被丢弃」的问题。
 */
class ProcessInvoiceOcrJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    // AiService::vision() 的 HTTP 超时为 120s，Job 超时必须留足
    public int $timeout = 120;

    public int $maxExceptions = 1;

    /** @var array<int, int> 重试退避秒数 */
    public array $backoff = [15, 60];

    public function __construct(public AiOcrResult $ocr)
    {
    }

    public function handle(AiService $aiService, NotificationService $notifications): void
    {
        $this->ocr->update([
            'status' => AiOcrResult::STATUS_PROCESSING,
            'started_at' => now(),
            'error_message' => null,
        ]);

        Log::info('开始处理发票 OCR', ['ocr_id' => $this->ocr->id]);

        $disk = Storage::disk($this->ocr->disk ?: 'local');

        if (! $this->ocr->path || ! $disk->exists($this->ocr->path)) {
            throw new \RuntimeException('OCR 原始图片不存在：' . ($this->ocr->path ?: '(未上传)'));
        }

        $imageBase64 = base64_encode($disk->get($this->ocr->path));

        $raw = $aiService->vision(
            $imageBase64,
            '从这张发票图片中提取以下信息，返回 JSON 格式：供应商名称、发票日期、金额明细（商品名称、数量、单价）。'
        );

        if (blank($raw)) {
            throw new \RuntimeException('AI 视觉识别未返回结果，请检查后台 AI 设置是否启用并填写了有效的 API Key');
        }

        $parsed = $this->extractJson($raw);

        $this->ocr->update([
            'status' => AiOcrResult::STATUS_SUCCESS,
            'result' => $parsed,
            'raw_response' => mb_substr($raw, 0, 20000),
            'finished_at' => now(),
        ]);

        Log::info('发票 OCR 处理完成', ['ocr_id' => $this->ocr->id]);

        if ($this->ocr->user_id) {
            $notifications->send(
                (int) $this->ocr->user_id,
                '发票识别完成',
                '你提交的发票《' . ($this->ocr->original_name ?: '未命名') . '》已识别完成，结果可查看。',
                'success',
                route('app.ai.ocr.show', $this->ocr->id),
                ['ocr_id' => $this->ocr->id]
            );
        }
    }

    /**
     * 重试前重置状态，避免记录停留在 processing
     */
    public function failed(?Throwable $exception = null): void
    {
        $message = $exception?->getMessage() ?: '未知错误';

        $this->ocr->update([
            'status' => AiOcrResult::STATUS_FAILED,
            'error_message' => $message,
            'finished_at' => now(),
        ]);

        Log::error('发票 OCR 处理失败', ['ocr_id' => $this->ocr->id, 'error' => $message]);

        if ($this->ocr->user_id) {
            app(NotificationService::class)->send(
                (int) $this->ocr->user_id,
                '发票识别失败',
                '发票《' . ($this->ocr->original_name ?: '未命名') . '》识别失败：' . $message,
                'danger',
                route('app.ai.ocr.show', $this->ocr->id),
                ['ocr_id' => $this->ocr->id]
            );
        }
    }

    /**
     * 从模型返回中抽取 JSON（兼容 ```json 代码块包裹）
     */
    protected function extractJson(string $raw): array
    {
        $text = trim($raw);

        if (preg_match('/```(?:json)?\s*(.+?)```/s', $text, $matches)) {
            $text = trim($matches[1]);
        }

        $decoded = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // 无法解析时保留原文，避免任务失败导致结果彻底丢失
            return ['raw' => $raw];
        }

        return is_array($decoded) ? $decoded : ['value' => $decoded];
    }
}
