@extends('layouts.admin')

@section('title', '权限管理')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">权限管理</h2>
        <a href="{{ route('admin.permissions.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">新建权限</a>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-3">权限名称</th>
                <th class="text-right py-3">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach($permissions as $permission)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3">{{ $permission->name }}</td>
                <td class="py-3 text-right">
                    <a href="{{ route('admin.permissions.edit', $permission) }}" class="text-blue-600 hover:text-blue-800 mr-2">编辑</a>
                    <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('确定删除？')">删除</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
