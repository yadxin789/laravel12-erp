<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemRequest;
use App\Models\MasterData\Item;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
     * 规格书操作页（查看 / 上传 / 删除）
     */
    public function specSheet(Item $item)
    {
        $this->authorize('uploadSpecSheet', $item);

        $size = null;
        $uploadedAt = null;
        if ($item->hasSpecSheet() && Storage::disk('public')->exists($item->spec_sheet_path)) {
            $size = Storage::disk('public')->size($item->spec_sheet_path);
            $uploadedAt = Storage::disk('public')->lastModified($item->spec_sheet_path);
        }

        return view('masterdata.items.spec-sheet', compact('item', 'size', 'uploadedAt'));
    }

    /**
     * 上传规格书：仅允许 PDF 格式
     */
    public function uploadSpecSheet(Request $request, Item $item)
    {
        $this->authorize('uploadSpecSheet', $item);

        $request->validate([
            'spec_sheet' => 'required|file|mimes:pdf|max:20480',
        ], [
            'spec_sheet.required' => '请选择要上传的规格书文件',
            'spec_sheet.file' => '上传的内容不是有效文件',
            'spec_sheet.mimes' => '规格书必须是 PDF 格式文件',
            'spec_sheet.max' => '规格书文件不能超过 20MB',
        ]);

        $file = $request->file('spec_sheet');

        // 双重校验：扩展名 + 文件头都必须为 PDF
        if (strtolower((string) $file->getClientOriginalExtension()) !== 'pdf' || ! $this->isPdfFile($file->getPathname())) {
            return back()->withErrors(['spec_sheet' => '规格书必须是 PDF 格式文件'])->withInput();
        }

        $oldPath = $item->spec_sheet_path;
        $path = $file->storeAs(
            'items/spec-sheets',
            $item->id . '-' . date('YmdHis') . '-' . Str::lower(Str::random(6)) . '.pdf',
            'public'
        );

        if (! $path) {
            return back()->with('error', '规格书上传失败，请稍后重试');
        }

        $oldValues = $item->toArray();
        $item->update(['spec_sheet_path' => $path]);

        if ($oldPath && $oldPath !== $path) {
            Storage::disk('public')->delete($oldPath);
        }

        AuditLogger::update('item', $item, $oldValues);

        return redirect()->route('admin.items.spec-sheet', $item)->with('success', '规格书上传成功');
    }

    /**
     * 在线预览规格书（浏览器内直接打开 PDF）
     */
    public function previewSpecSheet(Item $item)
    {
        $this->authorize('view', $item);

        $disk = Storage::disk('public');
        abort_unless($item->hasSpecSheet(), 404, '该物料尚未上传规格书');
        abort_unless($disk->exists($item->spec_sheet_path), 404, '规格书文件不存在');

        return response()->file($disk->path($item->spec_sheet_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="spec-sheet.pdf"',
        ]);
    }

    /**
     * 下载规格书
     */
    public function downloadSpecSheet(Item $item)
    {
        $this->authorize('view', $item);

        $disk = Storage::disk('public');
        abort_unless($item->hasSpecSheet(), 404, '该物料尚未上传规格书');
        abort_unless($disk->exists($item->spec_sheet_path), 404, '规格书文件不存在');

        return $disk->download($item->spec_sheet_path, '规格书-' . $item->code . '.pdf');
    }

    /**
     * 删除规格书
     */
    public function deleteSpecSheet(Item $item)
    {
        $this->authorize('uploadSpecSheet', $item);

        if ($item->hasSpecSheet()) {
            $oldValues = $item->toArray();
            Storage::disk('public')->delete($item->spec_sheet_path);
            $item->update(['spec_sheet_path' => null]);
            AuditLogger::update('item', $item, $oldValues);
        }

        return redirect()->route('admin.items.spec-sheet', $item)->with('success', '规格书已删除');
    }

    /**
     * 文件头是否为 PDF（%PDF-）
     */
    protected function isPdfFile(string $path): bool
    {
        if (! is_file($path)) {
            return false;
        }

        $handle = @fopen($path, 'rb');
        if (! $handle) {
            return false;
        }

        $magic = fread($handle, 5);
        fclose($handle);

        return $magic === '%PDF-';
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
