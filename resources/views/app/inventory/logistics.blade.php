@extends('layouts.business')

@section('title', '物流查询')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title mb-0"><i class="fa-solid fa-truck-fast"></i>物流查询</h2>
        <span class="badge {{ $channel && $channel->hasRealKey() ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' }}">
            {{ $channel && $channel->hasRealKey() ? '通道：' . $channel->name : '公共查询模式（未配置密钥）' }}
        </span>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3">
        {{-- 查询 --}}
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-magnifying-glass me-2"></i>运单查询</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('app.inventory.logistics.query') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="carrier">快递公司</label>
                            <select id="carrier" name="carrier" class="form-select" required>
                                @foreach ($carriers as $c)
                                    <option value="{{ $c->key }}" {{ request('carrier') === $c->key ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="tracking_no">运单号</label>
                            <input type="text" id="tracking_no" name="tracking_no" class="form-control font-monospace"
                                   required value="{{ request('tracking_no') }}" placeholder="如 SF1234567890">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa-solid fa-magnifying-glass me-1"></i>查询轨迹
                        </button>
                    </form>

                    <hr>

                    <div class="fw-semibold mb-2"><i class="fa-solid fa-file-circle-plus me-2"></i>登记新运单</div>
                    <form method="POST" action="{{ route('app.inventory.logistics.store') }}">
                        @csrf
                        <div class="row g-2">
                            <div class="col-12">
                                <select name="carrier" class="form-select form-select-sm" required>
                                    @foreach ($carriers as $c)
                                        <option value="{{ $c->key }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <input type="text" name="tracking_no" class="form-control form-control-sm font-monospace"
                                       placeholder="运单号" required>
                            </div>
                            <div class="col-6">
                                <input type="text" name="receiver_name" class="form-control form-control-sm" placeholder="收件人">
                            </div>
                            <div class="col-6">
                                <input type="text" name="receiver_phone" class="form-control form-control-sm" placeholder="联系电话">
                            </div>
                            <div class="col-12">
                                <input type="text" name="receiver_address" class="form-control form-control-sm" placeholder="收件地址">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                                    <i class="fa-solid fa-plus me-1"></i>登记并查询
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 结果 --}}
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-route me-2"></i>查询结果</div>
                <div class="card-body">
                    @if (! $result)
                        <p class="erp-empty mb-0">输入运单号查询，或点击右侧登记新运单。</p>
                    @else
                        @if ($result['is_demo'])
                            <div class="alert alert-warning py-2">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ $result['message'] }}
                            </div>
                        @elseif ($result['source'] === 'public')
                            <div class="alert alert-info py-2">
                                <i class="fa-solid fa-circle-info me-2"></i>数据来源：免费公共查询接口。
                                <a href="{{ route('admin.logistics.settings') }}" class="alert-link">配置正式通道</a>可提升稳定性。
                            </div>
                        @endif

                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                            <div>
                                <div class="fw-semibold font-monospace">{{ request('tracking_no') }}</div>
                                <div class="small text-muted">{{ $result['shipment']?->carrier_name }}</div>
                            </div>
                            <span class="badge border {{ \App\Models\Logistics\LogisticsShipment::STATUS_BADGES[$result['status']] ?? 'bg-light text-secondary border' }}">
                                {{ \App\Models\Logistics\LogisticsShipment::STATUS_LABELS[$result['status']] ?? $result['status'] }}
                            </span>
                        </div>

                        @if ($result['shipment'])
                            <a href="{{ route('app.inventory.logistics.show', $result['shipment']) }}" class="btn btn-sm btn-outline-secondary mb-3">
                                <i class="fa-solid fa-file-lines me-1"></i>运单详情
                            </a>
                        @endif

                        <ol class="list-unstyled mb-0 border-start ps-3">
                            @foreach (array_reverse($result['traces']) as $t)
                                <li class="mb-3 position-relative">
                                    <span class="position-absolute rounded-circle bg-primary"
                                          style="left:-21px; top:6px; width:10px; height:10px;"></span>
                                    <div class="small text-muted">{{ $t['time'] }}</div>
                                    <div>{{ $t['description'] }}</div>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 最近运单 --}}
    <div class="card mt-4">
        <div class="card-header"><i class="fa-solid fa-clock-rotate-left me-2"></i>最近运单</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>运单号</th>
                            <th>快递公司</th>
                            <th>状态</th>
                            <th>收件人</th>
                            <th>登记人</th>
                            <th>更新</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($shipments as $s)
                            <tr>
                                <td class="font-monospace">{{ $s->tracking_no }}</td>
                                <td>{{ $s->carrier_name ?: '—' }}</td>
                                <td><span class="badge border {{ $s->statusBadge() }}">{{ $s->statusLabel() }}</span></td>
                                <td>{{ $s->receiver_name ?: '—' }}</td>
                                <td>{{ $s->creator?->name ?? '—' }}</td>
                                <td class="small text-muted">{{ $s->last_traced_at?->format('m-d H:i') ?? '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('app.inventory.logistics.show', $s) }}" class="btn btn-sm btn-outline-primary">详情</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="erp-empty">暂无运单记录</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end p-3">
                {{ $shipments->links() }}
            </div>
        </div>
    </div>
@endsection
