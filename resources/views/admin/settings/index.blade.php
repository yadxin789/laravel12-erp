@extends('layouts.admin')

@section('title', '系统设置')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-gear"></i>系统设置</h2>
    </div>

    <div class="alert alert-info">
        <i class="fa-solid fa-circle-info me-2"></i>当前为基础参数展示，持久化保存接口尚未接入。
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-sliders me-2"></i>基础设置</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="system_name">系统名称</label>
                        <input type="text" id="system_name" class="form-control" value="电子工厂ERP系统" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="version">系统版本</label>
                        <input type="text" id="version" class="form-control" value="1.0.0" readonly>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="company_name">企业名称</label>
                        <input type="text" id="company_name" class="form-control" value="电子工厂有限公司" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header"><i class="fa-regular fa-clock me-2"></i>日期时间设置</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="date_format">日期格式</label>
                        <select id="date_format" class="form-select" disabled>
                            <option>YYYY-MM-DD</option>
                            <option>DD/MM/YYYY</option>
                            <option>MM/DD/YYYY</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="time_format">时间格式</label>
                        <select id="time_format" class="form-select" disabled>
                            <option>24小时制</option>
                            <option>12小时制</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="timezone">时区</label>
                        <select id="timezone" class="form-select" disabled>
                            <option>Asia/Shanghai (UTC+8)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
