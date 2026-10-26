<?php

namespace App\Http\Controllers\App\Sales;

use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Sales\Quotation;
use App\Models\MasterData\Customer;
use App\Models\MasterData\Item;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class QuotationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        Gate::authorize('view sales quotations');
        $quotations = Quotation::with('customer')->paginate(20);
        return view('app.sales.quotations.index', compact('quotations'));
    }

    public function create()
    {
        Gate::authorize('create sales quotations');
        $customers = Customer::all();
        $items = Item::all();
        return view('app.sales.quotations.create', compact('customers', 'items'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create sales quotations');
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'quotation_date' => 'required|date',
            'valid_until' => 'required|date',
            'lines' => 'required|array',
            'lines.*.item_id' => 'required|exists:items,id',
            'lines.*.quantity' => 'required|integer|min:1',
            'lines.*.unit_price' => 'required|numeric|min:0',
        ]);

        // 数据表字段为 quote_no / quote_date，与表单提交的 quotation_date 做映射
        $nextId = (int) (Quotation::withTrashed()->max('id') ?? 0) + 1;
        $quotation = Quotation::create([
            'quote_no' => 'QT' . date('Ymd') . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT),
            'customer_id' => $validated['customer_id'],
            'quote_date' => $validated['quotation_date'],
            'valid_until' => $validated['valid_until'],
            'status' => QuotationStatus::Draft,
        ]);

        foreach ($validated['lines'] as $line) {
            $quotation->lines()->create([
                'item_id' => $line['item_id'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'amount' => $line['quantity'] * $line['unit_price'],
            ]);
        }

        $this->auditLog('create', 'Quotation', $quotation);

        return redirect()->route('app.sales.quotations.show', $quotation)->with('success', '报价单创建成功');
    }

    public function show(Quotation $quotation)
    {
        Gate::authorize('view sales quotations');
        return view('app.sales.quotations.show', compact('quotation'));
    }

    public function submit(Quotation $quotation)
    {
        Gate::authorize('edit sales quotations');

        if (! $quotation->canSubmit()) {
            return back()->with('error', '报价单状态不允许提交');
        }

        $quotation->update(['status' => QuotationStatus::Submitted]);
        $this->auditLog('submit', 'Quotation', $quotation);

        $this->notificationService->sendToPermission(
            'approve sales quotations',
            '报价单已提交',
            "报价单 #{$quotation->id} 已提交，请审核。",
            'info',
            route('app.sales.quotations.show', $quotation)
        );

        return redirect()->route('app.sales.quotations.show', $quotation)->with('success', '报价单已提交');
    }

    public function convert(Request $request, Quotation $quotation)
    {
        Gate::authorize('edit sales quotations');

        if (! $quotation->canConvert()) {
            return back()->with('error', '报价单状态不允许生成销售订单');
        }

        $order = \App\Models\Sales\SalesOrder::create([
            'customer_id' => $quotation->customer_id,
            'order_date' => now()->format('Y-m-d'),
            'delivery_date' => $quotation->valid_until,
            'status' => SalesOrderStatus::Draft,
        ]);

        foreach ($quotation->lines as $line) {
            $order->lines()->create([
                'item_id' => $line->item_id,
                'quantity' => $line->quantity,
                'unit_price' => $line->unit_price,
                'amount' => $line->amount,
            ]);
        }

        $quotation->update(['status' => QuotationStatus::Converted]);
        $this->auditLog('convert', 'Quotation', $quotation);

        return redirect()->route('app.sales.orders.show', $order)->with('success', '已从报价单生成销售订单');
    }

    protected function auditLog(string $action, string $module, $record): void
    {
        \App\Models\System\AuditLog::create([
            'action' => $action,
            'module' => $module,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name,
            'record_id' => $record->id,
            'record_type' => get_class($record),
            'remark' => "{$module} #{$record->id} {$action}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
