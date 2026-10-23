@extends('layouts.admin')

@section('title', '编辑角色')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-user-shield"></i>编辑角色</h2>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.roles.update', $role) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="display_name">岗位名称（中文）</label>
                        <input type="text" id="display_name" name="display_name" class="form-control"
                               placeholder="{{ $catalog[$role->name]['label'] ?? '如：销售经理' }}"
                               value="{{ old('display_name', $role->display_name) }}">
                        <div class="form-text">
                            界面显示名；留空则使用岗位字典默认值
                            @if(isset($catalog[$role->name]))
                                「{{ $catalog[$role->name]['label'] }}」
                            @endif
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="name">角色标识 <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" required
                               value="{{ old('name', $role->name) }}">
                        <div class="form-text">英文小写下划线，修改后需同步代码中的角色判定</div>
                    </div>
                </div>

                @if(isset($catalog[$role->name]))
                    <div class="alert alert-light border mt-3 mb-0 small">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        岗位字典：{{ $catalog[$role->name]['label'] }} ·
                        所属部门 {{ $catalog[$role->name]['department'] }} ·
                        {{ $catalog[$role->name]['desc'] }}
                    </div>
                @endif

                <div class="mb-3 mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0">权限（已选 {{ $role->permissions->count() }} 项）</label>
                        <button type="button" id="toggle-all" class="btn btn-sm btn-outline-secondary">全选 / 取消</button>
                    </div>
                    @include('admin.roles._permissions', ['permissions' => $permissions, 'role' => $role])
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>更新</button>
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
