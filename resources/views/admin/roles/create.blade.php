@extends('layouts.admin')

@section('title', '新建角色')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-user-shield"></i>新建角色</h2>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.roles.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="display_name">岗位名称（中文）</label>
                        <input type="text" id="display_name" name="display_name" class="form-control"
                               placeholder="如：销售经理" value="{{ old('display_name') }}">
                        <div class="form-text">界面上显示的名称，留空则显示角色标识</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="name">角色标识 <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" required
                               placeholder="如：sales_manager" value="{{ old('name') }}">
                        <div class="form-text">英文小写下划线，用于代码与中间件判定，创建后不建议修改</div>
                    </div>
                </div>

                <div class="mb-3 mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0">权限</label>
                        <button type="button" id="toggle-all" class="btn btn-sm btn-outline-secondary">全选 / 取消</button>
                    </div>
                    @include('admin.roles._permissions', ['permissions' => $permissions])
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>创建</button>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.getElementById('toggle-all').addEventListener('click', function () {
    const boxes = document.querySelectorAll('.perm-check');
    const allChecked = Array.from(boxes).every((b) => b.checked);
    boxes.forEach((b) => { b.checked = !allChecked; });
});
</script>
@endpush
