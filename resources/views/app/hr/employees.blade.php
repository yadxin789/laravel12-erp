@extends('layouts.business')
@section('title', '员工管理')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold mb-4">员工管理</h2>
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-100">
            <th class="px-3 py-2">工号</th><th class="px-3 py-2">姓名</th><th class="px-3 py-2">部门</th>
            <th class="px-3 py-2">职位</th><th class="px-3 py-2">电话</th><th class="px-3 py-2">入职日期</th><th class="px-3 py-2">状态</th>
        </tr></thead>
        <tbody>
        @foreach($employees as $emp)
            <tr class="border-b">
                <td class="px-3 py-2 font-mono">{{ $emp->emp_no }}</td>
                <td class="px-3 py-2">{{ $emp->name }}</td>
                <td class="px-3 py-2">{{ $emp->department ?: '—' }}</td>
                <td class="px-3 py-2">{{ $emp->position ?: '—' }}</td>
                <td class="px-3 py-2">{{ $emp->phone ?: '—' }}</td>
                <td class="px-3 py-2">{{ $emp->hire_date ?: '—' }}</td>
                <td class="px-3 py-2"><span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">{{ $emp->status }}</span></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $employees->links() }}</div>
</div>
@endsection
