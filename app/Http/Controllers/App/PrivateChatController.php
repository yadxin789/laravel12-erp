<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Chat\ChatClearState;
use App\Models\Chat\ChatMessage;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * 私聊：任意两个用户之间
 */
class PrivateChatController extends Controller
{
    /**
     * 私聊页：左侧联系人 + 右侧对话
     */
    public function show(Request $request, int $userId)
    {
        $user = $request->user();
        $user->touchLastSeen();

        $target = User::findOrFail($userId);
        if ($target->id === $user->id) {
            return redirect()->route('app.chat')->with('error', '不能和自己私聊');
        }

        $messages = $this->conversation($user->id, $target->id);

        // 抵达到此页即视为已读：清除来自该用户的私聊通知
        \App\Models\System\Notification::where('user_id', $user->id)
            ->where('type', 'private_message')
            ->where('link', route('app.chat.private', $target->id))
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $contacts = User::where('id', '!=', $user->id)
            ->orderBy('name')
            ->get()
            ->map(function ($u) use ($user) {
                $last = ChatMessage::between($user->id, $u->id)->latest()->first();
                $u->last_message = $last?->content;
                $u->last_at = $last?->created_at;

                return $u;
            })
            ->sortByDesc(fn ($u) => $u->last_at?->timestamp ?? 0)
            ->values();

        return view('app.chat.private', compact('target', 'messages', 'contacts'));
    }

    /**
     * 发送私聊消息
     */
    public function send(Request $request, int $userId, NotificationService $notify)
    {
        $user = $request->user();
        $target = User::findOrFail($userId);

        $validated = $request->validate([
            'content' => 'nullable|string|max:2000',
            'file' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,txt,zip,rar',
        ]);

        if (empty($validated['content']) && ! $request->hasFile('file')) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'error' => '消息内容不能为空'], 422)
                : back()->with('error', '消息内容不能为空');
        }

        $path = null;
        $name = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $name = $file->getClientOriginalName();
            $path = 'private/' . date('Ymd') . '/' . uniqid('', true) . '.' . $file->getClientOriginalExtension();
            Storage::disk('chat')->put($path, file_get_contents($file->getRealPath()));
        }

        $message = ChatMessage::create([
            'user_id' => $user->id,
            'receiver_id' => $target->id,
            'is_private' => true,
            'content' => $validated['content'] ?? null,
            'file_path' => $path,
            'file_name' => $name,
        ]);

        // 对方不在线时，落一条站内通知
        if (! $target->isOnline()) {
            $notify->send(
                $target->id,
                '新的私聊消息',
                $user->name . '：' . mb_substr((string) ($validated['content'] ?: '[文件] ' . $name), 0, 100),
                'private_message',
                route('app.chat.private', $user->id)
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message->fresh()->toFrontend($user->id),
            ]);
        }

        return back()->with('success', '已发送');
    }

    /**
     * 私聊记录（JSON 轮询）
     */
    public function history(Request $request, int $userId): JsonResponse
    {
        $user = $request->user();
        $afterId = (int) $request->input('after', 0);

        $query = ChatMessage::between($user->id, $userId)
            ->with('user')
            ->withTrashed()
            ->afterClear($user->id, ChatClearState::conversationKeyForPrivate($userId));

        if ($afterId > 0) {
            $messages = $query->where('id', '>', $afterId)->orderBy('id')->get();
        } else {
            $messages = $query->orderByDesc('id')->take(300)->get()->reverse()->values();
        }
        $user->touchLastSeen();

        return response()->json([
            'success' => true,
            'messages' => $messages->map(fn ($m) => $m->toFrontend($user->id))->values(),
        ]);
    }

    /**
     * 联系人列表（JSON）
     */
    public function members(Request $request): JsonResponse
    {
        $user = $request->user();

        $members = User::where('id', '!=', $user->id)
            ->orderBy('name')
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'avatar_url' => $u->avatar_url,
                'initial' => $u->initial,
                'position' => $u->position,
                'online' => $u->isOnline(),
                'url' => route('app.chat.private', $u->id),
            ]);

        return response()->json(['success' => true, 'members' => $members]);
    }

    /**
     * 清空我看的这段私聊
     */
    public function clearMine(Request $request, int $userId)
    {
        ChatClearState::markCleared(
            $request->user()->id,
            'private',
            ChatClearState::conversationKeyForPrivate($userId),
            $userId
        );

        return back()->with('success', '已清空我看到的该私聊记录');
    }

    protected function conversation(int $me, int $other)
    {
        return ChatMessage::between($me, $other)
            ->with('user')
            ->withTrashed()
            ->afterClear($me, ChatClearState::conversationKeyForPrivate($other))
            ->orderBy('id', 'desc')
            ->take(300)
            ->get()
            ->reverse()
            ->values();
    }
}
