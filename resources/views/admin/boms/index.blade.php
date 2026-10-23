@extends('layouts.admin')

@section('title', 'BOM管理')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-sitemap"></i>BOM管理</h2>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>编码</th>
                            <th>物料名称</th>
                            <th>版本</th>
                            <th>生效日期</th>
                            <th>失效日期</th>
                            <th class="text-end">数量</th>
                            <th>状态</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($boms as $bom)
                            <tr>
                                <td class="font-monospace small">{{ $bom->code }}</td>
                                <td>{{ $bom->item->name ?? '-' }}</td>
                                <td>{{ $bom->version }}</td>
                                <td>{{ $bom->effective_date }}</td>
                                <td>{{ $bom->expiry_date ?? '-' }}</td>
                                <td class="text-end">{{ $bom->quantity }}</td>
                                <td><span class="badge bg-light text-secondary">{{ $bom->status }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="erp-empty">暂无 BOM 数据</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $boms->links() }}
            </div>
        </div>
    </div>
@endsection
