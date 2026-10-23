@extends('layouts.business')

@section('title', '我的头像')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title mb-0"><i class="fa-solid fa-user-circle"></i>我的头像</h2>
        <a href="{{ route('app.profile.edit') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-id-card me-1"></i>个人资料
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card text-center h-100">
                <div class="card-header"><i class="fa-solid fa-image-portrait me-2"></i>当前头像</div>
                <div class="card-body">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" class="rounded-circle border mb-3" width="120" height="120" alt="{{ $user->name }}">
                    @else
                        <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3"
                              style="width:120px;height:120px;font-size:2.5rem;">{{ $user->initial }}</span>
                    @endif

                    <div class="fw-semibold">{{ $user->name }}</div>
                    <div class="text-muted small">{{ $user->email }}</div>

                    @if ($user->is_default_avatar)
                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle mt-2">默认头像</span>
                    @elseif ($user->avatar)
                        <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle mt-2">已上传</span>
                    @else
                        <span class="badge bg-light text-secondary border mt-2">未设置</span>
                    @endif

                    @if ($user->avatar)
                        <form method="POST" action="{{ route('app.profile.avatar.reset') }}" class="mt-3"
                              onsubmit="return confirm('确定清除当前头像？')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fa-regular fa-trash-can me-1"></i>清除头像</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header"><i class="fa-solid fa-upload me-2"></i>上传自定义头像</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('app.profile.avatar.upload') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <input type="file" name="avatar" id="avatar-input" class="form-control" accept="image/png,image/jpeg,image/gif,image/webp" required>
                            <div class="form-text">支持 jpg / png / gif / webp，大小不超过 2MB</div>
                        </div>
                        <div id="preview" class="mb-3 d-none">
                            <img id="preview-img" src="" class="rounded-circle border" width="80" height="80" alt="预览">
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-upload me-1"></i>上传</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><i class="fa-solid fa-images me-2"></i>或选择系统默认头像</div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-3">
                        @foreach ($defaults as $d)
                            <form method="POST" action="{{ route('app.profile.avatar.upload') }}">
                                @csrf
                                <input type="hidden" name="default" value="{{ $loop->iteration }}">
                                <button type="submit" class="btn btn-link p-0 border-0">
                                    <img src="{{ $d['url'] }}" class="rounded-circle border {{ $user->avatar === $d['key'] ? 'border-primary border-2' : '' }}"
                                         width="72" height="72" alt="默认头像 {{ $loop->iteration }}">
                                </button>
                            </form>
                        @endforeach
                    </div>
                    <div class="form-text mt-2">点击即可应用，无需上传图片</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.getElementById('avatar-input')?.addEventListener('change', function (e) {
    const file = e.target.files?.[0];
    if (!file) return;
    const box = document.getElementById('preview');
    const img = document.getElementById('preview-img');
    img.src = URL.createObjectURL(file);
    box.classList.remove('d-none');
});
</script>
@endpush
