<?php

namespace App\Jobs;

use App\Services\AiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessInvoiceOcrJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 60;

    public function __construct(
        public string $imageBase64,
        public int $uploadedBy
    ) {
    }

    public function handle(AiService $aiService): array
    {
        Log::info("开始处理发票OCR...");

        $result = $aiService->vision(
            "从这张发票图片中提取以下信息，返回JSON格式：供应商名称、发票日期、金额明细（商品名称、数量、单价）。",
            $this->imageBase64
        );

        Log::info("发票OCR处理完成");
        return $result;
    }
}
