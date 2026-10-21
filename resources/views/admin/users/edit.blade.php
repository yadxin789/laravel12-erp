@extends('layouts.admin')

@section('title', '编辑用户')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">编辑用户</h2>
    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">用户名</label>
            <input type="text" name="name" class="w-full px-4 py-2 border rounded" value="{{ $user->name }}" required>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">邮箱</label>
            <input type="email" name="email" class="w-full px-4 py-2 border rounded" value="{{ $user->email }}" required>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">密码（留空不修改）</label>
            <input type="password" name="password" class="w-full px-4 py-2 border rounded">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">角色</label>
            <select name="role" class="w-full px-4 py-2 border rounded" required>
                @foreach($roles as $role)
                <option value="{{ $role->name }}" {{ $user->roles->contains($role) ? 'selected' : '' }}>{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">更新</button>
    </form>
</div>
@endsection
