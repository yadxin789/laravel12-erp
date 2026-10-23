@extends('layouts.business')

@section('title', '经营日报')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-newspaper"></i>今日经营日报</h2>
        <a href="{{ route('app.ai.analysis-center') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-xl-9">
            <div class="card border-primary-subtle">
                <div class="card-body">
                    <div class="alert alert-primary mb-0" style="white-space: pre-wrap;">{{ $report }}</div>
                </div>
                <div class="card-footer text-muted small">
                    <i class="fa-regular fa-clock me-1"></i>生成时间：{{ now()->format('Y-m-d H:i') }}
                </div>
            </div>
        </div>
    </div>
@endsection
