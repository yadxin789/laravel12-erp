@extends('layouts.admin')

@section('title', '角色管理')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">角色管理</h2>
        <a href="{{ route('admin.roles.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">新建角色</a>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-3">角色名称</th>
                <th class="text-left py-3">权限数量</th>
                <th class="text-right py-3">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach($roles as $role)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3">{{ $role->name }}</td>
                <td class="py-3">{{ $role->permissions->count() }}</td>
                <td class="py-3 text-right">
                    <a href="{{ route('admin.roles.edit', $role) }}" class="text-blue-600 hover:text-blue-800 mr-2">编辑</a>
                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('确定删除？')">删除</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $roles->links() }}
</div>
@endsection
