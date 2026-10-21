@extends('layouts.admin')

@section('title', '系统设置')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">系统设置</h2>
    </div>

    <div class="grid grid-cols-2 gap-6">
        <!-- 基础设置 -->
        <div class="border rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">基础设置</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">系统名称</label>
                    <input type="text" value="电子工厂ERP系统" class="w-full px-3 py-2 border rounded">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">系统版本</label>
                    <input type="text" value="1.0.0" class="w-full px-3 py-2 border rounded">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">企业名称</label>
                    <input type="text" value="电子工厂有限公司" class="w-full px-3 py-2 border rounded">
                </div>
            </div>
        </div>

        <!-- 日期时间设置 -->
        <div class="border rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">日期时间设置</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">日期格式</label>
                    <select class="w-full px-3 py-2 border rounded">
                        <option>YYYY-MM-DD</option>
                        <option>DD/MM/YYYY</option>
                        <option>MM/DD/YYYY</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">时间格式</label>
                    <select class="w-full px-3 py-2 border rounded">
                        <option>24小时制</option>
                        <option>12小时制</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">时区</label>
                    <select class="w-full px-3 py-2 border rounded">
                        <option>Asia/Shanghai (UTC+8)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <button class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">保存设置</button>
    </div>
</div>
@endsection