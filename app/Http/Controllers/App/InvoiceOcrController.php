<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessInvoiceOcrJob;
use App\Models\System\AiSetting;
use App\Models\System\AiOcrResult;
use App\Services\AiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InvoiceOcrController extends Controller
{
    /**
     * 发票识别页面
     */
    public function index()
    {
        return view('app.ai.invoice-ocr');
    }

    /**
     * 异步识别入口：入队后立即返回，前端凭 ID 轮询结果
     *
     * 识别一次可能耗时数十秒，走队列可避免 PHP-FPM 请求被长时间占用。
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'invoice_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $setting = AiSetting::current();
        if (! $setting->enabled || ! $setting->vision_enabled) {
            return response()->json([
                'message' => 'AI 视觉功能未启用，请先在后台 AI 设置中开启视觉/OCR。',
            ], 422);
        }

        $file = $request->file('invoice_image');
        $path = $file->store('invoices', 'public');

        $ocr = AiOcrResult::create([
            'user_id' => $request->user()?->id,
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'status' => AiOcrResult::STATUS_PENDING,
        ]);

        ProcessInvoiceOcrJob::dispatch($ocr);

        return response()->json([
            'id' => $ocr->id,
            'status' => $ocr->status,
            'status_url' => route('app.ai.ocr.show', $ocr->id),
        ], 202);
    }

    /**
     * 查询异步识别结果
     */
    public function show(Request $request, AiOcrResult $ocr): JsonResponse
    {
        if ($ocr->user_id && (int) $ocr->user_id !== (int) $request->user()?->id
            && ! $request->user()?->hasRole('admin')) {
            abort(403);
        }

        return response()->json([
            'id' => $ocr->id,
            'status' => $ocr->status,
            'status_label' => $ocr->statusLabel(),
            'result' => $ocr->result,
            'error_message' => $ocr->error_message,
            'original_name' => $ocr->original_name,
            // 图片存于 public 盘，经 storage:link 对外可访问
            'image_url' => $ocr->path ? asset('storage/' . ltrim($ocr->path, '/')) : null,
        ]);
    }

    /**
     * 上传发票并识别
     */
    public function recognize(Request $request, AiService $ai)
    {
        $request->validate([
            'invoice_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        // 检查 AI 视觉是否可用
        $setting = \App\Models\System\AiSetting::current();
        if (!$setting->enabled || !$setting->vision_enabled) {
            return back()->with('error', 'AI 视觉功能未启用。请先在后台 AI 设置中开启视觉/OCR。');
        }

        // 读取图片并转 base64
        $imageContent = file_get_contents($request->file('invoice_image')->getRealPath());
        $base64 = base64_encode($imageContent);

        $prompt = <<<PROMPT
请识别这张采购发票/账单图片，提取以下信息并以 JSON 格式返回（不要返回其他内容）：
{
  "supplier_name": "供应商名称",
  "invoice_date": "发票日期 YYYY-MM-DD",
  "invoice_no": "发票号码",
  "total_amount": 总金额数字,
  "tax_amount": 税额数字,
  "items": [
    {"item_name": "物料名称", "spec": "规格", "quantity": 数量, "unit_price": 单价, "amount": 行金额}
  ]
}
如果某项无法识别，填 null。金额为纯数字，不要带货币符号。
PROMPT;

        $result = $ai->vision($base64, $prompt);

        if (!$result) {
            return back()->with('error', 'AI 识别失败，请检查 API 配置或换一张更清晰的图片');
        }

        // 清理 AI 返回的 markdown 代码块
        $jsonStr = trim($result);
        $jsonStr = preg_replace('/^```json\s*/m', '', $jsonStr);
        $jsonStr = preg_replace('/^```\s*/m', '', $jsonStr);
        $jsonStr = trim($jsonStr);

        $data = json_decode($jsonStr, true);

        if (!$data) {
            return back()->with('error', 'AI 返回格式异常，原始结果：' . mb_substr($result, 0, 200));
        }

        // 保存图片到附件
        $path = $request->file('invoice_image')->store('invoices', 'public');

        return view('app.ai.invoice-ocr-result', [
            'data' => $data,
            'imagePath' => $path,
            'raw' => $result,
        ]);
    }
}
