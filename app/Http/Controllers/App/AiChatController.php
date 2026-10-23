<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\System\AiAttachment;
use App\Models\System\AiConversation;
use App\Models\System\AiMessage;
use App\Models\System\AiModel;
use App\Models\System\AiProvider;
use App\Models\System\AiSetting;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * 用户 AI 聊天：多会话 + 模型切换 + 附件
 */
class AiChatController extends Controller
{
    /**
     * 聊天页：会话列表 + 当前会话消息
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // 管理员只管理 AI（供应商 / 模型 / 限流），不使用 AI 聊天
        if ($user?->hasRole('admin')) {
            return redirect()->route('admin.ai-settings.index')
                ->with('warning', '管理员账号用于管理 AI（供应商、模型、调用限流等），不提供 AI 聊天功能，请使用业务账号登录使用 AI。');
        }

        $setting = AiSetting::current();
        $provider = AiProvider::active();

        // 管理员未开通（未启用 / 没有可用供应商与密钥）→ 「AI 暂未开通」提示页
        if (! $setting->enabled || ! $provider || ! $provider->hasRealApiKey()) {
            return redirect()->route('app.ai.notice');
        }

        $userId = auth()->id();
        $conversations = AiConversation::withoutTrashed()
            ->where('user_id', $userId)
            ->orderBy('is_pinned', 'desc')
            ->orderBy('last_message_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $conversation = null;
        if ($request->filled('conversation')) {
            $conversation = AiConversation::where('user_id', $userId)->find($request->input('conversation'));
        }
        $conversation = $conversation ?: $conversations->first();

        $messages = $conversation
            ? $conversation->messages()->with('attachments')->orderBy('id')->get()
            : collect();

        $models = AiModel::usable()->with('provider')->orderBy('provider_id')->orderBy('sort_order')->get();
        $currentModel = $conversation?->model_id
            ? AiModel::find($conversation->model_id)
            : AiModel::usableDefault();

        // 按角色加载技能包：系统提示词 / 工具 / 快捷问题都不同
        $skills = \App\Support\AiSkills::for($request->user());
        $tools = $skills['tools'];
        $quickPrompts = $skills['prompts'];

        return view('app.ai.chat', compact(
            'setting', 'provider', 'conversations', 'conversation', 'messages', 'models', 'currentModel', 'tools', 'quickPrompts', 'skills'
        ));
    }

    /**
     * 「AI 暂未开通」提示页（管理员尚未配置并启用 AI 时展示）
     */
    public function notice()
    {
        return view('app.ai.notice');
    }

    /**
     * 提问（JSON）
     */
    public function ask(Request $request, AiService $ai)
    {
        $setting = AiSetting::current();
        $user = $request->user();

        // 管理员不使用 AI 聊天
        if ($user?->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'error' => '管理员账号仅用于管理 AI 设置，不提供 AI 聊天功能，请使用业务账号使用 AI。',
            ], 403);
        }

        if (! $setting->enabled) {
            return response()->json([
                'success' => false,
                'error' => 'AI 尚未开通，请联系系统管理员在「AI 智能助手」中配置至少一个供应商与模型。',
            ], 403);
        }

        // 限流：每分钟 / 每天
        $minuteKey = 'ai:' . $user->id;
        $dailyKey = 'ai-daily:' . $user->id;
        if (RateLimiter::tooManyAttempts($minuteKey, $setting->rate_limit_minute)) {
            return response()->json([
                'success' => false,
                'error' => '请求过于频繁，请稍后再试（每分钟 ' . $setting->rate_limit_minute . ' 次）。',
            ], 429);
        }
        if (RateLimiter::tooManyAttempts($dailyKey, $setting->rate_limit_daily)) {
            return response()->json([
                'success' => false,
                'error' => '已达到今日调用上限（' . $setting->rate_limit_daily . ' 次）。',
            ], 429);
        }

        $validated = $request->validate([
            'message' => 'nullable|string|max:4000',
            'conversation_id' => 'nullable|integer',
            'model_id' => 'nullable|integer',
            'files' => 'nullable|array|max:5',
            'files.*' => 'file|max:10240|mimetypes:image/jpeg,image/png,image/gif,image/webp,text/plain,text/csv,application/pdf,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);

        $message = trim((string) ($validated['message'] ?? ''));
        if ($message === '' && ! $request->hasFile('files')) {
            return response()->json(['success' => false, 'error' => '请输入消息内容。'], 422);
        }

        // 会话
        $conversation = null;
        if (! empty($validated['conversation_id'])) {
            $conversation = AiConversation::where('user_id', $user->id)->find($validated['conversation_id']);
        }
        if (! $conversation) {
            $conversation = AiConversation::create([
                'user_id' => $user->id,
                'title' => mb_substr($message, 0, 40) ?: '新会话',
                'model_id' => $validated['model_id'] ?? null,
            ]);
        }

        // 模型
        $model = ! empty($validated['model_id'])
            ? AiModel::usable()->find($validated['model_id'])
            : ($conversation->model_id ? AiModel::find($conversation->model_id) : AiModel::usableDefault());
        $model = $model ?: AiModel::usableDefault();
        if ($model) {
            $conversation->update(['model_id' => $model->id]);
        }

        // 附件
        $attachments = collect();
        foreach ($request->file('files', []) as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }
            $path = 'attachments/' . $user->id . '/' . date('Ymd') . '/' . Str::random(40) . '.' . $file->getClientOriginalExtension();
            Storage::disk('ai_chat')->put($path, file_get_contents($file->getRealPath()));

            $mime = $file->getMimeType();
            $isImage = str_starts_with((string) $mime, 'image/');
            $text = null;
            if (! $isImage && in_array($mime, ['text/plain', 'text/csv', 'application/csv'], true)) {
                $text = mb_convert_encoding(file_get_contents($file->getRealPath()), 'UTF-8', 'auto');
            }

            $attachments->push(AiAttachment::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'type' => $isImage ? 'image' : 'file',
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $mime,
                'size' => $file->getSize(),
                'text_extracted' => ! empty($text),
                'extracted_text' => $text ? mb_substr($text, 0, 50000) : null,
            ]));
        }

        // 用户消息
        $userMessage = $conversation->addMessage('user', $message !== '' ? $message : '[图片]', [
            'model' => $model?->model_id,
            'attachments' => $attachments->map(fn ($a) => [
                'id' => $a->id,
                'type' => $a->type,
                'name' => $a->original_name,
                'url' => route('app.ai.chat.attachments.preview', $a),
                'download_url' => route('app.ai.chat.attachments.download', $a),
                'is_image' => $a->is_image,
            ])->all(),
        ]);
        $conversation->autoTitle($message);
        $attachments->each(fn ($a) => $a->update(['message_id' => $userMessage->id]));

        RateLimiter::hit($minuteKey, 60);
        RateLimiter::hit($dailyKey, 86400);

        // 历史（最近 20 条）
        $history = $conversation->messages()->orderBy('id')->get()
            ->where('id', '<', $userMessage->id)
            ->take(-20)
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
            ->values()
            ->all();

        try {
            $service = (new AiService($setting));
            if ($model) {
                $service->useModel($model);
            }
            // 角色化系统提示词（该角色关注什么、能看哪些数据、怎么回答）
            $systemPrompt = \App\Support\AiSkills::for($user)['system_prompt'];
            $reply = $service->chatWithAttachments($message, $history, $attachments->all(), $systemPrompt);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('AI 聊天异常: ' . $e->getMessage());
            $reply = null;
        }

        if (empty($reply)) {
            return response()->json([
                'success' => false,
                'error' => 'AI 服务暂时不可用，请检查后台供应商配置或稍后重试。',
                'conversation_id' => $conversation->id,
            ], 502);
        }

        $assistantMessage = $conversation->addMessage('assistant', $reply, [
            'model' => $model?->model_id,
            'model_name' => $model?->name,
            'provider' => $model?->provider?->name,
        ]);

        return response()->json([
            'success' => true,
            'reply' => $reply,
            'data' => null,
            'conversation_id' => $conversation->id,
            'conversation_title' => $conversation->title,
            'message_id' => $assistantMessage->id,
            'model' => $model?->model_id ?? $setting->model,
            'model_name' => $model?->name ?? $setting->model,
            'timestamp' => now()->format('H:i'),
        ]);
    }

    /**
     * 新建会话
     */
    public function storeConversation(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:200',
            'model_id' => 'nullable|integer|exists:ai_models,id',
        ]);

        $conversation = AiConversation::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'] ?? '新会话',
            'model_id' => $validated['model_id'] ?? AiModel::usableDefault()?->id,
        ]);

        return redirect()->route('app.ai.chat', ['conversation' => $conversation->id]);
    }

    public function renameConversation(Request $request, AiConversation $conversation)
    {
        abort_unless($conversation->user_id === auth()->id(), 403);

        $validated = $request->validate(['title' => 'required|string|max:200']);
        $conversation->update(['title' => $validated['title']]);

        return back()->with('success', '会话已重命名');
    }

    public function destroyConversation(AiConversation $conversation)
    {
        abort_unless($conversation->user_id === auth()->id(), 403);
        $conversation->delete();

        return redirect()->route('app.ai.chat')->with('success', '会话已删除');
    }

    /**
     * 清空会话内容（保留会话）
     */
    public function clearConversation(AiConversation $conversation)
    {
        abort_unless($conversation->user_id === auth()->id(), 403);

        $conversation->messages()->delete();
        $conversation->update(['message_count' => 0, 'last_message_at' => now()]);

        return redirect()->route('app.ai.chat', ['conversation' => $conversation->id])
            ->with('success', '会话内容已清空');
    }

    /**
     * 导出会话（Markdown）
     */
    public function exportConversation(AiConversation $conversation)
    {
        abort_unless($conversation->user_id === auth()->id(), 403);

        $content = "# {$conversation->title}\n\n";
        foreach ($conversation->messages as $m) {
            $who = $m->role === 'user' ? '我' : 'AI';
            $content .= "**{$who}**（{$m->created_at?->format('Y-m-d H:i')}）\n\n{$m->content}\n\n---\n\n";
        }

        return response($content, 200, [
            'Content-Type' => 'text/markdown; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="ai-conversation-' . $conversation->id . '.md"',
        ]);
    }

    public function previewAttachment(AiAttachment $attachment)
    {
        abort_unless($attachment->user_id === auth()->id(), 403);

        return response()->file($attachment->absolutePath());
    }

    public function downloadAttachment(AiAttachment $attachment)
    {
        abort_unless($attachment->user_id === auth()->id(), 403);

        return response()->download($attachment->absolutePath(), $attachment->original_name);
    }
}
