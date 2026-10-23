<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * 个人资料 + 头像（上传 / 默认头像选择）
 */
class ProfileController extends Controller
{
    /**
     * 资料页
     */
    public function edit()
    {
        return view('app.profile.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'position' => ['nullable', 'string', 'max:100'],
            'signature' => ['nullable', 'string', 'max:500'],
        ], [], [
            'name' => '姓名',
            'email' => '邮箱',
            'phone' => '联系电话',
            'position' => '岗位',
            'signature' => '个性签名',
        ]);

        $user->update($validated);

        return back()->with('success', '个人资料已更新');
    }

    /**
     * 头像设置页：上传自定义头像 / 选择默认头像
     */
    public function avatar()
    {
        $defaults = collect(range(1, 5))->map(fn ($i) => [
            'key' => 'defaults/avatar' . $i . '.svg',
            'url' => asset('avatars/defaults/avatar' . $i . '.svg'),
        ]);

        return view('app.profile.avatar', ['user' => auth()->user(), 'defaults' => $defaults]);
    }

    public function uploadAvatar(Request $request)
    {
        $user = $request->user();

        // 方式一：选择默认头像
        if ($request->filled('default')) {
            $request->validate([
                'default' => ['required', 'integer', 'min:1', 'max:5'],
            ]);

            $this->deleteUploadedAvatar($user);
            $user->update(['avatar' => 'defaults/avatar' . (int) $request->input('default') . '.svg']);

            return back()->with('success', '已选择默认头像');
        }

        // 方式二：上传图片
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ], [
            'avatar.required' => '请先选择要上传的图片',
            'avatar.image' => '只能上传图片文件',
            'avatar.mimes' => '仅支持 jpg/png/gif/webp 格式',
            'avatar.max' => '图片不能超过 2MB',
        ]);

        $file = $request->file('avatar');
        $filename = 'user_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();

        // public 盘：storage/app/public/avatars/xxx → /storage/avatars/xxx
        Storage::disk('public')->putFileAs('avatars', $file, $filename);

        $this->deleteUploadedAvatar($user);
        $user->update(['avatar' => $filename]);

        return back()->with('success', '头像已更新');
    }

    /**
     * 清除已上传的头像（回退到首字母占位）
     */
    public function resetAvatar(Request $request)
    {
        $user = $request->user();
        $this->deleteUploadedAvatar($user);
        $user->update(['avatar' => null]);

        return back()->with('success', '已清除头像');
    }

    /**
     * 删除旧的自上传头像文件（默认头像不动）
     */
    protected function deleteUploadedAvatar($user): void
    {
        if (empty($user->avatar) || str_starts_with($user->avatar, 'defaults/')) {
            return;
        }
        Storage::disk('public')->delete('avatars/' . $user->avatar);
    }
}
