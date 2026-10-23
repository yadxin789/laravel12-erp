{{-- 附件上传组件 --}}
{{-- 用法: @include('components.attachment-section', ['model' => $model]) --}}
<div class="card mt-4">
    <div class="card-header"><i class="fa-solid fa-paperclip me-2"></i>附件 / 文档</div>
    <div class="card-body">
        <form method="POST" action="{{ route('attachments.upload') }}" enctype="multipart/form-data" class="mb-3">
            @csrf
            <input type="hidden" name="attachable_type" value="{{ get_class($model) }}">
            <input type="hidden" name="attachable_id" value="{{ $model->id }}">
            <div class="d-flex flex-wrap gap-2">
                <input type="file" name="file" required class="form-control flex-fill" style="min-width: 12rem;">
                <input type="text" name="description" placeholder="备注（可选）" class="form-control" style="max-width: 14rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-upload me-1"></i>上传
                </button>
            </div>
            <div class="form-text">支持：图片、PDF、Word、Excel、ZIP，最大 10MB</div>
        </form>

        @if($model->attachments->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle table-sm">
                    <thead>
                        <tr>
                            <th>文件名</th>
                            <th>大小</th>
                            <th>备注</th>
                            <th>上传人</th>
                            <th>时间</th>
                            <th class="text-end">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($model->attachments as $att)
                            <tr>
                                <td>
                                    @if($att->isImage())
                                        <img src="{{ $att->url }}" class="rounded me-2" width="28" height="28" style="object-fit: cover;" alt="{{ $att->filename }}">
                                    @else
                                        <i class="fa-regular fa-file-lines text-secondary me-2"></i>
                                    @endif
                                    {{ $att->filename }}
                                </td>
                                <td class="text-muted">{{ $att->size_human }}</td>
                                <td>{{ $att->description ?: '—' }}</td>
                                <td class="text-muted">{{ $att->uploaded_by ?: '—' }}</td>
                                <td class="text-muted">{{ $att->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('attachments.download', $att) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-download me-1"></i>下载
                                    </a>
                                    <form action="{{ route('attachments.destroy', $att) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('确定删除？')">
                                            <i class="fa-regular fa-trash-can me-1"></i>删除
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="erp-empty mb-0">暂无附件</p>
        @endif
    </div>
</div>
