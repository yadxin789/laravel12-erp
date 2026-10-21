{{-- 附件上传组件 --}}
{{-- 用法: @include('components.attachment-section', ['model' => $model]) --}}
<div class="bg-white rounded-lg shadow p-6 mt-4">
    <h3 class="text-lg font-bold mb-4">附件 / 文档</h3>

    {{-- 上传表单 --}}
    <form method="POST" action="{{ route('attachments.upload') }}" enctype="multipart/form-data" class="mb-4">
        @csrf
        <input type="hidden" name="attachable_type" value="{{ get_class($model) }}">
        <input type="hidden" name="attachable_id" value="{{ $model->id }}">
        <div class="flex gap-2">
            <input type="file" name="file" required class="flex-1 border border-gray-300 rounded px-3 py-2">
            <input type="text" name="description" placeholder="备注（可选）" class="w-48 border border-gray-300 rounded px-3 py-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">上传</button>
        </div>
        <p class="text-xs text-gray-500 mt-1">支持: 图片、PDF、Word、Excel、ZIP，最大 10MB</p>
    </form>

    {{-- 附件列表 --}}
    @if($model->attachments->count() > 0)
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-3 py-2 text-left">文件名</th>
                    <th class="px-3 py-2 text-left">大小</th>
                    <th class="px-3 py-2 text-left">备注</th>
                    <th class="px-3 py-2 text-left">上传人</th>
                    <th class="px-3 py-2 text-left">时间</th>
                    <th class="px-3 py-2 text-center">操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($model->attachments as $att)
                    <tr class="border-b">
                        <td class="px-3 py-2">
                            @if($att->isImage())
                                <img src="{{ $att->url }}" class="w-8 h-8 object-cover rounded inline mr-2">
                            @else
                                <span class="mr-2">📄</span>
                            @endif
                            {{ $att->filename }}
                        </td>
                        <td class="px-3 py-2 text-gray-500">{{ $att->size_human }}</td>
                        <td class="px-3 py-2">{{ $att->description ?: '—' }}</td>
                        <td class="px-3 py-2 text-gray-500">{{ $att->uploaded_by ?: '—' }}</td>
                        <td class="px-3 py-2 text-gray-500">{{ $att->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-3 py-2 text-center">
                            <a href="{{ route('attachments.download', $att) }}" class="text-blue-600 hover:underline">下载</a>
                            <form action="{{ route('attachments.destroy', $att) }}" method="POST" class="inline ml-2">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('确定删除？')">删除</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-gray-400 text-sm">暂无附件</p>
    @endif
</div>
