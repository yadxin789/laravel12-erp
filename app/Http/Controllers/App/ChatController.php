<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Chat\ChatClearState;
use App\Models\Chat\ChatMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * 群聊：所有已登录用户均可参与
 */
class ChatController extends Controller
{
    /**
     * 群聊页
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $user->touchLastSeen();

        $messages = ChatMessage::group()
            ->with('user')
            ->withTrashed()
            ->afterClear($user->id, ChatClearState::conversationKeyForGroup())
            ->orderBy('id', 'desc')
            ->take(300)
            ->get()
            ->reverse()
            ->values();

        $members = User::orderBy('name')->get();

        return view('app.chat.index', compact('messages', 'members'));
    }

    /**
     * 发送群消息（支持文件）
     */
    public function send(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'content' => 'nullable|string|max:2000',
            'file' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,txt,zip,rar',
        ]);

        if (empty($validated['content']) && ! $request->hasFile('file')) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => '消息内容不能为空'], 422);
            }

            return back()->with('error', '消息内容不能为空');
        }

        $path = null;
        $name = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $name = $file->getClientOriginalName();
            $path = 'group/' . date('Ymd') . '/' . uniqid('', true) . '.' . $file->getClientOriginalExtension();
            Storage::disk('chat')->put($path, file_get_contents($file->getRealPath()));
        }

        $message = ChatMessage::create([
            'user_id' => $user->id,
            'receiver_id' => null,
            'is_private' => false,
            'content' => $validated['content'] ?? null,
            'file_path' => $path,
            'file_name' => $name,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message->fresh()->toFrontend($user->id),
            ]);
        }

        return back()->with('success', '已发送');
    }

    /**
     * 群消息列表（JSON，用于轮询刷新）
     */
    public function messages(Request $request): JsonResponse
    {
        $user = $request->user();
        $afterId = (int) $request->input('after', 0);

        $query = ChatMessage::group()
            ->with('user')
            ->withTrashed()
            ->afterClear($user->id, ChatClearState::conversationKeyForGroup())
            ->orderBy('id');

        if ($afterId > 0) {
            $query->where('id', '>', $afterId);
        } else {
            $query->orderByDesc('id')->take(300);
        }

        $messages = $query->get();
        if ($afterId === 0) {
            $messages = $messages->reverse()->values();
        }
        $user->touchLastSeen();

        return response()->json([
            'success' => true,
            'messages' => $messages->map(fn ($m) => $m->toFrontend($user->id))->values(),
        ]);
    }

    /**
     * 撤回/删除自己的消息
     */
    public function destroy(Request $request, ChatMessage $message)
    {
        if ($message->user_id !== $request->user()->id) {
            abort(403, '只能删除自己的消息');
        }
        $message->delete();

        return response()->json(['success' => true]);
    }

    /**
     * 清空我看到的群聊记录
     */
    public function clearMine(Request $request)
    {
        ChatClearState::markCleared(
            $request->user()->id,
            'group',
            ChatClearState::conversationKeyForGroup()
        );

        return back()->with('success', '已清空我看到的群聊记录');
    }

    /**
     * 下载聊天附件
     */
    public function file(ChatMessage $message)
    {
        abort_unless($message->file_path, 404);

        return Storage::disk('chat')->download($message->file_path, $message->file_name);
    }
}
