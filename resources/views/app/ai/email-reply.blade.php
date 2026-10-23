@extends('layouts.business')

@section('title', '邮件智能回复')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-envelope-open-text"></i>邮件智能回复</h2>
        <a href="{{ route('app.ai.analysis-center') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-xl-9">
            <div class="card mb-4">
                <div class="card-body">
                    <form method="POST" action="{{ route('app.ai.email-reply') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="customer_id">客户</label>
                            <select id="customer_id" name="customer_id" class="form-select">
                                <option value="">—</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="inquiry">客户询盘内容 <span class="text-danger">*</span></label>
                            <textarea id="inquiry" name="inquiry" required rows="5" class="form-control"
                                      placeholder="粘贴客户邮件内容...">{{ old('inquiry') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i>AI 生成回复草稿
                        </button>
                    </form>
                </div>
            </div>

            @if($draft)
                <div class="card border-primary-subtle">
                    <div class="card-header"><i class="fa-solid fa-robot me-2"></i>AI 回复草稿</div>
                    <div class="card-body">
                        <div class="alert alert-primary mb-0" style="white-space: pre-wrap;">{{ $draft }}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
