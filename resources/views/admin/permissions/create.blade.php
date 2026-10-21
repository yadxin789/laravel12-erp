@extends('layouts.admin')

@section('title', '新建权限')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">新建权限</h2>
    <form method="POST" action="{{ route('admin.permissions.store') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">权限名称</label>
            <input type="text" name="name" class="w-full px-4 py-2 border rounded" required>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">创建</button>
    </form>
</div>
@endsection
