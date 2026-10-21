@extends('layouts.business')
@section('title', '考勤记录')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold mb-4">考勤记录</h2>
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-100">
            <th class="px-3 py-2">日期</th><th class="px-3 py-2">员工</th>
            <th class="px-3 py-2">签到</th><th class="px-3 py-2">签退</th><th class="px-3 py-2">状态</th>
        </tr></thead>
        <tbody>
        @foreach($attendances as $a)
            <tr class="border-b">
                <td class="px-3 py-2">{{ $a->date }}</td>
                <td class="px-3 py-2">{{ $a->employee?->name }}</td>
                <td class="px-3 py-2">{{ $a->check_in?->format('H:i') ?: '—' }}</td>
                <td class="px-3 py-2">{{ $a->check_out?->format('H:i') ?: '—' }}</td>
                <td class="px-3 py-2"><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">{{ $a->status }}</span></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $attendances->links() }}</div>
</div>
@endsection
