<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\System\Attachment;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    /**
     * 上传附件（通用）
     * POST /attachments/upload
     * 参数: attachable_type (模型类名), attachable_id, file, description
     */
    public function upload(Request $request)
    {
        $request->validate([
            'attachable_type' => 'required|string',
            'attachable_id' => 'required|integer',
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,webp,pdf,doc,docx,xls,xlsx,csv,txt,zip|max:10240',
            'description' => 'nullable|string|max:255',
        ]);

        $modelClass = $request->attachable_type;
        // 安全检查：只允许 App\Models 下的模型
        if (!str_starts_with($modelClass, 'App\\Models\\')) {
            return back()->with('error', '无效的模型类型');
        }

        $model = $modelClass::findOrFail($request->attachable_id);

        $attachment = $model->addAttachment($request->file('file'), $request->description);

        return back()->with('success', '附件上传成功: ' . $attachment->filename);
    }

    /**
     * 删除附件
     */
    public function destroy(Attachment $attachment)
    {
        $this->authorize('delete', $attachment);
        $attachment->delete();
        return back()->with('success', '附件已删除');
    }

    /**
     * 下载附件
     */
    public function download(Attachment $attachment)
    {
        return \Storage::disk('public')->download($attachment->path, $attachment->filename);
    }
}
