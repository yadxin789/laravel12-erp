<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemRequest;
use App\Models\MasterData\Item;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Item::class);

        $query = Item::with('category', 'unit')
            ->where('active', true);

        if ($request->filled('code')) {
            $query->where('code', 'like', "%{$request->code}%");
        }
        if ($request->filled('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        }
        if ($request->filled('barcode')) {
            $query->where('barcode', 'like', "%{$request->barcode}%");
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $items = $query->paginate(20);

        return $request->expectsJson()
            ? response()->json($items)
            : view('masterdata.items.index', compact('items'));
    }

    public function create()
    {
        $this->authorize('create', Item::class);

        return view('masterdata.items.create');
    }

    public function store(ItemRequest $request)
    {
        $this->authorize('create', Item::class);

        $data = $request->validated();

        // 处理图片上传
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('items', 'public');
        }

        // 自动生成条码
        $item = Item::create($data);
        $item->getOrCreateBarcode();

        AuditLogger::create('item', $item);

        return redirect()->route('admin.items.index')->with('success', '物料创建成功');
    }

    public function show(Item $item, Request $request)
    {
        $this->authorize('view', $item);

        if ($request->expectsJson()) {
            return response()->json($item->load(['category', 'unit']));
        }

        return view('masterdata.items.show', compact('item'));
    }

    public function edit(Item $item)
    {
        $this->authorize('update', $item);

        return view('masterdata.items.edit', compact('item'));
    }

    public function update(ItemRequest $request, Item $item)
    {
        $this->authorize('update', $item);

        $oldValues = $item->toArray();
        $data = $request->validated();

        // 处理图片上传
        if ($request->hasFile('image')) {
            // 删除旧图片
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            $data['image_path'] = $request->file('image')->store('items', 'public');
        }

        $item->update($data);
        $item->getOrCreateBarcode();

        AuditLogger::update('item', $item, $oldValues);

        return redirect()->route('admin.items.index')->with('success', '物料更新成功');
    }

    public function destroy(Item $item)
    {
        $this->authorize('delete', $item);

        // 删除图片
        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }

        AuditLogger::delete('item', $item);
        $item->delete();

        return redirect()->route('admin.items.index')->with('success', '物料删除成功');
    }

    /**
     * 单个物料条码标签页
     */
    public function barcodeLabel(Item $item)
    {
        $this->authorize('view', $item);

        return view('masterdata.items.barcode-label', compact('item'));
    }

    /**
     * 批量条码标签页
     */
    public function barcodeLabels(Request $request)
    {
        $this->authorize('viewAny', Item::class);

        $items = Item::where('active', true)
            ->when($request->filled('category_id'), fn($q) => $q->where('category_id', $request->category_id))
            ->limit(100)
            ->get();

        return view('masterdata.items.barcode-labels', compact('items'));
    }

    /**
     * API: 按条码查询物料
     */
    public function getByBarcode($barcode)
    {
        $item = Item::where('barcode', $barcode)
            ->orWhere('code', $barcode)
            ->where('active', true)
            ->with(['category', 'unit'])
            ->first();

        if (!$item) {
            return response()->json(['error' => '物料不存在'], 404);
        }

        return response()->json([
            'id' => $item->id,
            'code' => $item->code,
            'barcode' => $item->barcode,
            'name' => $item->name,
            'spec' => $item->spec,
            'category' => $item->category?->name,
            'unit' => $item->unit?->name,
            'image_url' => $item->image_url,
        ]);
    }
}
