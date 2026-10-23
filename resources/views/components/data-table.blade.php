<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span>{{ $title }}</span>
        @if($createRoute ?? false)
            <a href="{{ $createRoute }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i>新建
            </a>
        @endif
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>{{ $header }}</tr>
                </thead>
                <tbody>{{ $body }}</tbody>
            </table>
        </div>

        @if(isset($pagination))
            <div class="d-flex justify-content-end">
                {{ $pagination }}
            </div>
        @endif
    </div>
</div>
