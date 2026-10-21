@extends('layouts.admin')

@section('title', '审计日志')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">审计日志</h2>
    </div>
    
    <div class="mb-4">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="flex gap-4">
            <input type="text" name="search" placeholder="搜索操作描述..." class="flex-1 px-4 py-2 border rounded-lg">
            <select name="action_type" class="px-4 py-2 border rounded-lg">
                <option value="">所有操作类型</option>
                <option value="create">新增</option>
                <option value="update">修改</option>
                <option value="delete">删除</option>
                <option value="login">登录</option>
                <option value="logout">登出</option>
            </select>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">搜索</button>
        </form>
    </div>

    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-3">时间</th>
                <th class="text-left py-3">操作人</th>
                <th class="text-left py-3">操作类型</th>
                <th class="text-left py-3">模块</th>
                <th class="text-left py-3">操作描述</th>
                <th class="text-left py-3">IP地址</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3">{{ $log->created_at }}</td>
                <td class="py-3">{{ $log->user->name ?? '-' }}</td>
                <td class="py-3">
                    <span class="px-2 py-1 rounded text-sm" 
                          :class="{
                              'bg-green-100 text-green-800': $log->action_type == 'create',
                              'bg-blue-100 text-blue-800': $log->action_type == 'update',
                              'bg-red-100 text-red-800': $log->action_type == 'delete',
                              'bg-yellow-100 text-yellow-800': $log->action_type == 'login',
                              'bg-gray-100 text-gray-800': $log->action_type == 'logout'
                          }">
                        @if($log->action_type == 'create')新增
                        @elseif($log->action_type == 'update')修改
                        @elseif($log->action_type == 'delete')删除
                        @elseif($log->action_type == 'login')登录
                        @elseif($log->action_type == 'logout')登出
                        @else{{ $log->action_type }}@endif
                    </span>
                </td>
                <td class="py-3">{{ $log->module }}</td>
                <td class="py-3">{{ $log->description }}</td>
                <td class="py-3">{{ $log->ip_address }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $logs->links() }}
</div>
@endsection