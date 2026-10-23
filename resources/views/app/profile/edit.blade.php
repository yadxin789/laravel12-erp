@extends('layouts.business')

@section('title', '个人资料')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title mb-0"><i class="fa-solid fa-id-card"></i>个人资料</h2>
        <a href="{{ route('app.profile.avatar') }}" class="btn btn-outline-primary btn-sm">
            <i class="fa-solid fa-user-circle me-1"></i>设置头像
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
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><i class="fa-solid fa-pen-to-square me-2"></i>基本信息</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('app.profile.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="name">姓名 <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name" class="form-control" required
                                       value="{{ old('name', $user->name) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="email">邮箱 <span class="text-danger">*</span></label>
                                <input type="email" id="email" name="email" class="form-control" required
                                       value="{{ old('email', $user->email) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="phone">联系电话</label>
                                <input type="text" id="phone" name="phone" class="form-control"
                                       value="{{ old('phone', $user->phone) }}" placeholder="如 138-0000-0000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="position">岗位 / 职位</label>
                                <input type="text" id="position" name="position" class="form-control"
                                       value="{{ old('position', $user->position) }}" placeholder="如 仓库主管">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="signature">个性签名 / 简介</label>
                                <textarea id="signature" name="signature" class="form-control" rows="3"
                                          maxlength="500" placeholder="一句话介绍自己">{{ old('signature', $user->signature) }}</textarea>
                                <div class="form-text">最多 500 字</div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>保存</button>
                            <a href="{{ route('app.dashboard') }}" class="btn btn-light">返回</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-circle-user me-2"></i>我的名片</div>
                <div class="card-body text-center">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" class="rounded-circle border mb-3" width="96" height="96" alt="{{ $user->name }}">
                    @else
                        <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3"
                              style="width:96px;height:96px;font-size:2rem;">{{ $user->initial }}</span>
                    @endif

                    <div class="fs-5 fw-semibold">{{ $user->name }}</div>
                    <div class="text-muted small mb-2">{{ $user->position ?: '未填写岗位' }}</div>

                    <div class="d-flex flex-wrap gap-1 justify-content-center mb-3">
                        @foreach ($user->roles as $role)
                            <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">{{ $role->name }}</span>
                        @endforeach
                    </div>

                    <ul class="list-group list-group-flush small text-start">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">邮箱</span><span>{{ $user->email }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">电话</span><span>{{ $user->phone ?: '—' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">状态</span>
                            <span>
                                @if ($user->isOnline())
                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">在线</span>
                                @else
                                    <span class="badge bg-light text-secondary border">离线</span>
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">最近活跃</span>
                            <span>{{ $user->last_seen_at?->format('Y-m-d H:i') ?? '—' }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
