@extends('layouts.app')

@section('title', '库存流水')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold">库存流水</h2>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="GET" class="flex flex-wrap gap-4 mb-6">
            <input type="text" name="transaction_no" placeholder="流水单号" value="{{ request('transaction_no') }}" class="border border-gray-300 rounded px-3 py-2">
            <select name="transaction_type" class="border border-gray-300 rounded px-3 py-2">
                <option value="">全部类型</option>
                <option value="purchase_receive">采购收货</option>
                <option value="purchase_return">采购退货</option>
                <option value="production_issue">生产领料</option>
                <option value="production_return">生产退料</option>
                <option value="production_receipt">生产入库</option>
                <option value="sales_issue">销售出库</option>
                <option value="sales_return">销售退货</option>
                <option value="transfer">库存调拨</option>
                <option value="adjust">库存调整</option>
                <option value="freeze">冻结</option>
                <option value="unfreeze">解冻</option>
            </select>
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="border border-gray-300 rounded px-3 py-2">
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="border border-gray-300 rounded px-3 py-2">
            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">搜索</button>
        </form>

        <table class="w-full">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 text-left">流水单号</th>
                    <th class="px-4 py-2 text-left">类型</th>
                    <th class="px-4 py-2 text-left">物料</th>
                    <th class="px-4 py-2 text-left">仓库</th>
                    <th class="px-4 py-2 text-center">数量</th>
                    <th class="px-4 py-2 text-left">批次号</th>
                    <th class="px-4 py-2 text-left">关联单号</th>
                    <th class="px-4 py-2 text-left">创建时间</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $transaction)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $transaction->transaction_no }}</td>
                        <td class="px-4 py-2">{{ $transaction->type_label }}</td>
                        <td class="px-4 py-2">{{ $transaction->item?->name }}</td>
                        <td class="px-4 py-2">{{ $transaction->warehouse?->name }}</td>
                        <td class="px-4 py-2 text-center">{{ $transaction->quantity }}</td>
                        <td class="px-4 py-2">{{ $transaction->batch_number }}</td>
                        <td class="px-4 py-2">{{ $transaction->reference_no }}</td>
                        <td class="px-4 py-2">{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $transactions->links() }}
    </div>
@endsection
