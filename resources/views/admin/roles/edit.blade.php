@extends('layouts.admin')

@section('title', '编辑角色')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">编辑角色</h2>
    <form method="POST" action="{{ route('admin.roles.update', $role) }}">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">角色名称</label>
            <input type="text" name="name" class="w-full px-4 py-2 border rounded" value="{{ $role->name }}" required>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">权限</label>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                @foreach($permissions as $permission)
                <label class="flex items-center">
                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" {{ $role->permissions->contains($permission) ? 'checked' : '' }}>
                    <span class="ml-2 text-sm">{{ $permission->name }}</span>
                </label>
                @endforeach
            </div>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">更新</button>
    </form>
</div>
@endsection
