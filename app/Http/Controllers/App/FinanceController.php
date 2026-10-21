<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Finance\CustomerInvoice;
use App\Models\Finance\SupplierBill;
use App\Models\Finance\Payment;
use App\Models\MasterData\Customer;
use App\Models\MasterData\Supplier;
use App\Services\NotificationService;
use App\Support\NumberGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    protected NotificationService $notifications;

    public function __construct(NotificationService $notifications)
    {
        $this->notifications = $notifications;
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->hasAnyRole(['admin', 'accountant', 'sales_manager', 'purchase_manager'])) {
                abort(403, '无财务模块访问权限');
            }
            return $next($request);
        });
    }

    // ========== 应收（客户发票） ==========

    public function invoices()
    {
        $invoices = CustomerInvoice::with('customer')->latest('invoice_date')->paginate(20);
        return view('app.finance.invoices.index', compact('invoices'));
    }

    public function createInvoice()
    {
        $customers = Customer::where('active', true)->get();
        return view('app.finance.invoices.create', compact('customers'));
    }

    public function storeInvoice(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'subtotal' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'remark' => 'nullable|string',
        ]);

        $validated['invoice_no'] = NumberGenerator::next('INV');
        $validated['tax_amount'] = $validated['tax_amount'] ?? 0;
        $validated['total_amount'] = $validated['subtotal'] + $validated['tax_amount'];
        $validated['status'] = 'unpaid';
        $validated['created_by'] = auth()->id();

        CustomerInvoice::create($validated);
        $invoice = CustomerInvoice::latest('id')->first();
        $this->auditLog('create', 'CustomerInvoice', $invoice);
        $this->notifications->sendToRole('accountant', '新客户发票', "发票 {$invoice->invoice_no} 已创建，金额 ¥{$invoice->total_amount}", 'info', route('app.finance.invoices'));

        return redirect()->route('app.finance.invoices')->with('success', '客户发票已创建');
    }

    // ========== 应付（供应商账单） ==========

    public function bills()
    {
        $bills = SupplierBill::with('supplier')->latest('bill_date')->paginate(20);
        return view('app.finance.bills.index', compact('bills'));
    }

    public function createBill()
    {
        $suppliers = Supplier::where('active', true)->get();
        return view('app.finance.bills.create', compact('suppliers'));
    }

    public function storeBill(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'bill_date' => 'required|date',
            'due_date' => 'nullable|date',
            'subtotal' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'remark' => 'nullable|string',
        ]);

        $validated['bill_no'] = NumberGenerator::next('BILL');
        $validated['tax_amount'] = $validated['tax_amount'] ?? 0;
        $validated['total_amount'] = $validated['subtotal'] + $validated['tax_amount'];
        $validated['status'] = 'unpaid';
        $validated['created_by'] = auth()->id();

        SupplierBill::create($validated);
        $bill = SupplierBill::latest('id')->first();
        $this->auditLog('create', 'SupplierBill', $bill);

        return redirect()->route('app.finance.bills')->with('success', '供应商账单已创建');
    }

    // ========== 收付款 ==========

    public function payments()
    {
        $payments = Payment::with(['customer', 'supplier'])->latest('payment_date')->paginate(20);
        return view('app.finance.payments.index', compact('payments'));
    }

    public function createPayment()
    {
        $customers = Customer::where('active', true)->get();
        $suppliers = Supplier::where('active', true)->get();
        $invoices = CustomerInvoice::where('status', '!=', 'paid')->get();
        $bills = SupplierBill::where('status', '!=', 'paid')->get();
        return view('app.finance.payments.create', compact('customers', 'suppliers', 'invoices', 'bills'));
    }

    public function storePayment(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:receipt,payment',
            'customer_id' => 'nullable|exists:customers,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'customer_invoice_id' => 'nullable|exists:customer_invoices,id',
            'supplier_bill_id' => 'nullable|exists:supplier_bills,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string',
            'reference_no' => 'nullable|string',
            'remark' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $validated['payment_no'] = NumberGenerator::next('PAY');
            $validated['created_by'] = auth()->id();

            $payment = Payment::create($validated);
            $this->auditLog('create', 'Payment', $payment);
            $this->notifications->sendToRole('accountant', '收付款登记', "收款/付款 ¥{$payment->amount} 已登记（单号 {$payment->payment_no}）", 'info', route('app.finance.payments'));

            // 更新发票/账单的已付金额和状态
            if ($payment->customer_invoice_id) {
                $inv = CustomerInvoice::find($payment->customer_invoice_id);
                $inv->paid_amount += $payment->amount;
                $inv->status = $inv->paid_amount >= $inv->total_amount ? 'paid' : 'partial';
                $inv->save();
            }

            if ($payment->supplier_bill_id) {
                $bill = SupplierBill::find($payment->supplier_bill_id);
                $bill->paid_amount += $payment->amount;
                $bill->status = $bill->paid_amount >= $bill->total_amount ? 'paid' : 'partial';
                $bill->save();
            }
        });

        return redirect()->route('app.finance.payments')->with('success', '收付款记录已保存');
    }

    // ========== 对账单 ==========

    public function statements()
    {
        // 应收账款汇总
        $arSummary = CustomerInvoice::select('customer_id',
            DB::raw('SUM(total_amount) as total'),
            DB::raw('SUM(paid_amount) as paid'),
            DB::raw('SUM(total_amount - paid_amount) as balance'))
            ->groupBy('customer_id')
            ->with('customer')
            ->get();

        // 应付账款汇总
        $apSummary = SupplierBill::select('supplier_id',
            DB::raw('SUM(total_amount) as total'),
            DB::raw('SUM(paid_amount) as paid'),
            DB::raw('SUM(total_amount - paid_amount) as balance'))
            ->groupBy('supplier_id')
            ->with('supplier')
            ->get();

        return view('app.finance.statements', compact('arSummary', 'apSummary'));
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
            'remark' => "{$action} {$module} #{$record->id}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
