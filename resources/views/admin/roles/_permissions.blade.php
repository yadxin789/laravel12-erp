@php
    $grouped = $permissions->groupBy('group');
    $groupLabels = \App\Http\Controllers\Admin\PermissionController::GROUPS;
    $checked = isset($role) ? $role->permissions->pluck('id')->all() : [];
@endphp

<div class="border rounded p-3" style="max-height: 22rem; overflow-y: auto;">
    @foreach($grouped as $group => $perms)
        <div class="fw-semibold small text-muted mt-2 mb-1">
            {{ $groupLabels[$group] ?? ($group ?: '未分组') }}
        </div>
        <div class="row g-2">
            @foreach($perms as $permission)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="form-check">
                        <input class="form-check-input perm-check" type="checkbox" name="permissions[]"
                               value="{{ $permission->name }}" id="perm-{{ $permission->id }}"
                               {{ in_array($permission->id, $checked) ? 'checked' : '' }}>
                        <label class="form-check-label small" for="perm-{{ $permission->id }}"
                               title="{{ $permission->name }}">
                            {{ $permission->display ?: $permission->name }}
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
</div>
