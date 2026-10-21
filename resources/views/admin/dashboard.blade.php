@extends('layouts.admin')

@section('title', '系统概览')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">系统概览</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-blue-50 p-4 rounded-lg">
            <p class="text-blue-600 text-sm">用户总数</p>
            <p class="text-2xl font-bold text-blue-800">{{ \App\Models\User::count() }}</p>
        </div>
        <div class="bg-green-50 p-4 rounded-lg">
            <p class="text-green-600 text-sm">角色总数</p>
            <p class="text-2xl font-bold text-green-800">{{ \Spatie\Permission\Models\Role::count() }}</p>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg">
            <p class="text-purple-600 text-sm">权限总数</p>
            <p class="text-2xl font-bold text-purple-800">{{ \Spatie\Permission\Models\Permission::count() }}</p>
        </div>
        <div class="bg-orange-50 p-4 rounded-lg">
            <p class="text-orange-600 text-sm">物料总数</p>
            <p class="text-2xl font-bold text-orange-800">{{ \App\Models\MasterData\Item::count() }}</p>
        </div>
    </div>
</div>
@endsection
