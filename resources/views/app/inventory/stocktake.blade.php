@extends('layouts.business')

@section('title', '库存盘点')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">库存盘点</h2>
    <form method="POST" action="{{ route('app.inventory.stocktake.store') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">仓库</label>
            <select name="warehouse_id" class="w-full px-4 py-2 border rounded" required>
                <option value="">选择仓库</option>
                @foreach($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                @endforeach
            </select>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">盘点明细</label>
            <div id="stocktake-lines">
                <div class="grid grid-cols-3 gap-4 mb-2">
                    <select name="lines[0][item_id]" class="px-4 py-2 border rounded" required>
                        <option value="">选择物料</option>
                        @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->name }} - {{ $item->spec }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="lines[0][actual_quantity]" placeholder="实际数量" class="px-4 py-2 border rounded" required>
                    <button type="button" onclick="removeLine(this)" class="bg-red-500 text-white px-4 py-2 rounded">删除</button>
                </div>
            </div>
            <button type="button" onclick="addLine()" class="bg-gray-600 text-white px-4 py-2 rounded">添加物料</button>
        </div>
        
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">确认盘点</button>
    </form>
</div>

<script>
let lineIndex = 1;
function addLine() {
    const container = document.getElementById('stocktake-lines');
    const html = `
        <div class="grid grid-cols-3 gap-4 mb-2">
            <select name="lines[${lineIndex}][item_id]" class="px-4 py-2 border rounded" required>
                <option value="">选择物料</option>
                @foreach($items as $item)
                <option value="{{ $item->id }}">{{ $item->name }} - {{ $item->spec }}</option>
                @endforeach
            </select>
            <input type="number" name="lines[${lineIndex}][actual_quantity]" placeholder="实际数量" class="px-4 py-2 border rounded" required>
            <button type="button" onclick="removeLine(this)" class="bg-red-500 text-white px-4 py-2 rounded">删除</button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    lineIndex++;
}
function removeLine(btn) {
    btn.parentElement.remove();
}
</script>
@endsection
