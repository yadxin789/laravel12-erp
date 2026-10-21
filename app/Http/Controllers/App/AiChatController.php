<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Services\AiService;
use App\Services\AiToolService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AiChatController extends Controller
{
    /**
     * 聊天页面
     */
    public function index()
    {
        $setting = \App\Models\System\AiSetting::current();
        if (!$setting->enabled || empty($setting->api_key) || str_starts_with($setting->api_key, 'sk-xxxx')) {
            return redirect()->route('app.ai.welcome');
        }
        $history = Session::get('ai_chat_history', []);
        return view('app.ai.chat', compact('history'));
    }

    /**
     * 发送消息
     */
    public function ask(Request $request, AiService $ai, AiToolService $tools)
    {
        $request->validate(['message' => 'required|string|max:1000']);

        $history = Session::get('ai_chat_history', []);

        // 限制历史长度（最近 10 轮 = 20 条消息）
        if (count($history) > 20) {
            $history = array_slice($history, -20);
        }

        $reply = $ai->setTools($tools)->chatWithTools($request->message, $history);

        // 存历史
        $history[] = ['role' => 'user', 'content' => $request->message];
        $history[] = ['role' => 'assistant', 'content' => $reply ?: '抱歉，暂无回复'];
        Session::put('ai_chat_history', $history);

        return response()->json(['reply' => $reply]);
    }

    /**
     * 清空对话
     */
    public function clear()
    {
        Session::forget('ai_chat_history');
        return back()->with('success', '对话已清空');
    }

    /**
     * AI 审批建议（供订单详情页 AJAX 调用）
     */
    public function approvalSuggestion(Request $request, AiService $ai)
    {
        $request->validate([
            'order_type' => 'required|string|in:sales,purchase',
            'order_id' => 'required|integer',
        ]);

        if ($request->order_type === 'sales') {
            $order = \App\Models\Sales\SalesOrder::with(['customer', 'lines.item'])->findOrFail($request->order_id);
            $orderData = [
                '订单号' => $order->order_no,
                '客户' => $order->customer?->name,
                '总金额' => $order->total_amount,
                '状态' => $order->status,
                '交货日期' => $order->delivery_date,
                '明细' => $order->lines->map(fn($l) => "{$l->item?->name} x{$l->quantity} @ ¥{$l->unit_price}"),
            ];
            $context = [
                '客户信用额度' => $order->customer?->credit_limit,
                '客户应收余额' => $order->customer?->balance ?? 0,
            ];
        } else {
            $order = \App\Models\Purchasing\PurchaseOrder::with(['supplier', 'lines.item'])->findOrFail($request->order_id);
            $orderData = [
                '订单号' => $order->order_no,
                '供应商' => $order->supplier?->name,
                '总金额' => $order->total_amount,
                '状态' => $order->status,
                '交货日期' => $order->delivery_date,
                '明细' => $order->lines->map(fn($l) => "{$l->item?->name} x{$l->quantity} @ ¥{$l->unit_price}"),
            ];
            $context = [];
        }

        $suggestion = $ai->analyzeApproval($request->order_type === 'sales' ? '销售订单' : '采购订单', $orderData, $context);

        return response()->json(['suggestion' => $suggestion ?: 'AI 未配置，无法提供建议']);
    }
}
