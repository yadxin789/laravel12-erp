<?php

namespace Database\Seeders;

use App\Models\Asset\Asset;
use App\Models\Crm\FollowUp;
use App\Models\Crm\Lead;
use App\Models\Crm\Opportunity;
use App\Models\Ecommerce\EcommerceOrder;
use App\Models\Finance\CustomerInvoice;
use App\Models\Finance\Payment;
use App\Models\Finance\SupplierBill;
use App\Models\HR\Attendance;
use App\Models\HR\Employee;
use App\Models\MasterData\Customer;
use App\Models\MasterData\Item;
use App\Models\MasterData\Supplier;
use App\Models\MasterData\Warehouse;
use App\Models\MasterData\WorkCenter;
use App\Models\Production\MaterialIssue;
use App\Models\Production\ProductionOrder;
use App\Models\Production\ProductionPlan;
use App\Models\Production\ProductionReceipt;
use App\Models\Production\ProductionReport;
use App\Models\Project\Project;
use App\Models\Project\ProjectTask;
use App\Models\Project\TimeEntry;
use App\Models\Purchasing\PurchaseOrder;
use App\Models\Purchasing\PurchaseRequisition;
use App\Models\Purchasing\PurchaseReturn;
use App\Models\Purchasing\Receipt;
use App\Models\Quality\DefectCode;
use App\Models\Quality\DefectRecord;
use App\Models\Quality\QualityInspection;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesReturn;
use App\Models\Sales\Shipment;
use App\Models\System\AiSetting;
use App\Models\System\ApprovalFlow;
use App\Models\System\ApprovalTask;
use App\Models\System\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * 扩展演示数据：财务 / CRM / 审批流 / HR / 项目 / 资产 / 电商 / 各业务单据明细
 * 全部采用幂等方式：表内已有数据则跳过。
 */
class ExtendedDemoDataSeeder extends Seeder
{
    private ?User $admin = null;

    public function run(): void
    {
        $this->admin = User::where('email', 'admin@admin.com')->first() ?? User::first();

        $this->createQuotations();
        $this->createShipments();
        $this->createSalesReturns();
        $this->createPurchaseRequisitions();
        $this->createPurchaseReceipts();
        $this->createPurchaseReturns();
        $this->createProductionPlans();
        $this->createMaterialIssues();
        $this->createProductionReports();
        $this->createProductionReceipts();
        $this->createQualityExtras();
        $this->createFinance();
        $this->createCrm();
        $this->createApprovals();
        $this->createHr();
        $this->createProjects();
        $this->createAssets();
        $this->createEcommerce();
        $this->createNotifications();
        $this->createAiSetting();

        $this->command?->info('扩展演示数据创建完成！');
    }

    private function skipIf(string $model): bool
    {
        return $model::query()->exists();
    }

    private function uid(): ?int
    {
        return $this->admin?->id;
    }

    // ==================== 销售 ====================

    private function createQuotations(): void
    {
        if ($this->skipIf(Quotation::class)) {
            return;
        }

        $customers = Customer::orderBy('id')->take(3)->get();
        $items = Item::whereIn('code', ['F-001', 'F-002', 'S-001', 'P-001'])->get()->keyBy('code');

        $rows = [
            ['QT-2026-003001', 'C-001', '2026-07-15', '2026-08-15', 'draft', [['F-001', 2, 3600.00]]],
            ['QT-2026-003002', 'C-002', '2026-07-12', '2026-08-12', 'submitted', [['F-002', 1, 5800.00], ['S-001', 4, 220.00]]],
            ['QT-2026-003003', 'C-003', '2026-07-05', '2026-08-05', 'won', [['F-001', 6, 3550.00], ['P-001', 20, 12.00]]],
        ];

        foreach ($rows as [$no, $custCode, $date, $valid, $status, $lines]) {
            $customer = $customers->firstWhere('code', $custCode);
            if (!$customer) {
                continue;
            }
            $amount = 0;
            $tax = 0;
            $lineRows = [];
            foreach ($lines as $ln => [$itemCode, $qty, $price]) {
                $item = $items->get($itemCode);
                if (!$item) {
                    continue;
                }
                $lineAmount = $qty * $price;
                $lineTax = round($lineAmount * 0.13, 2);
                $amount += $lineAmount;
                $tax += $lineTax;
                $lineRows[] = [
                    'line_number' => ($ln + 1) * 10,
                    'item_id' => $item->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'tax_rate' => 13,
                    'line_amount' => $lineAmount,
                    'line_tax' => $lineTax,
                ];
            }

            $q = Quotation::forceCreate([
                'quote_no' => $no,
                'customer_id' => $customer->id,
                'quote_date' => $date,
                'valid_until' => $valid,
                'status' => $status,
                'total_amount' => $amount,
                'total_tax' => $tax,
                'total_net_amount' => $amount + $tax,
                'currency' => 'CNY',
                'created_by' => $this->uid(),
            ]);
            foreach ($lineRows as $line) {
                $q->lines()->create($line);
            }
        }
    }

    private function createShipments(): void
    {
        if ($this->skipIf(Shipment::class)) {
            return;
        }

        $orders = SalesOrder::whereIn('status', ['approved', 'partially_delivered', 'fully_delivered'])->orderBy('id')->get();
        $fgWarehouse = Warehouse::where('code', 'WH-FG')->first();
        $fgItem = Item::where('code', 'F-001')->first();

        $statuses = ['draft', 'confirmed', 'shipped'];
        $i = 0;
        foreach ($orders as $order) {
            $shipment = Shipment::forceCreate([
                'shipment_no' => 'SH-2026-00' . (3001 + $i),
                'customer_id' => $order->customer_id,
                'sales_order_id' => $order->id,
                'shipment_date' => now()->subDays(10 - $i)->toDateString(),
                'warehouse_id' => $fgWarehouse?->id,
                'status' => $statuses[$i % count($statuses)],
                'created_by' => $this->uid(),
                'approved_at' => $i > 0 ? now()->subDays(2) : null,
            ]);
            $shipment->lines()->create([
                'line_number' => 10,
                'item_id' => $fgItem?->id,
                'quantity' => 2,
                'batch_number' => 'B2026070' . ($i + 1),
            ]);
            $i++;
        }
    }

    private function createSalesReturns(): void
    {
        if ($this->skipIf(SalesReturn::class)) {
            return;
        }

        $order = SalesOrder::where('status', 'fully_delivered')->first() ?? SalesOrder::first();
        if (!$order) {
            return;
        }
        $item = Item::where('code', 'F-001')->first();

        foreach (['draft', 'approved'] as $idx => $status) {
            $ret = SalesReturn::forceCreate([
                'return_no' => 'SR-2026-00' . (4001 + $idx),
                'customer_id' => $order->customer_id,
                'sales_order_id' => $order->id,
                'return_date' => now()->subDays(5 - $idx)->toDateString(),
                'status' => $status,
                'total_amount' => 3500,
                'reason' => $idx === 0 ? '客户要求换货' : '质量问题退货',
                'created_by' => $this->uid(),
                'approved_at' => $status === 'approved' ? now()->subDay() : null,
            ]);
            $ret->lines()->create([
                'line_number' => 10,
                'item_id' => $item?->id,
                'quantity' => 1,
                'batch_number' => 'B20260701',
                'remark' => '外包装破损',
            ]);
        }
    }

    // ==================== 采购 ====================

    private function createPurchaseRequisitions(): void
    {
        if ($this->skipIf(PurchaseRequisition::class)) {
            return;
        }

        $items = Item::whereIn('code', ['M-001', 'M-005'])->get()->keyBy('code');
        $supplier = Supplier::first();

        $rows = [
            ['PR-2026-0005001', 'draft', ['M-001' => 500]],
            ['PR-2026-0005002', 'approved', ['M-001' => 800, 'M-005' => 40]],
        ];

        foreach ($rows as [$no, $status, $qtyMap]) {
            $req = PurchaseRequisition::forceCreate([
                'req_no' => $no,
                'source_type' => 'manual',
                'status' => $status,
                'remark' => '根据库存预警补充采购',
                'created_by' => $this->uid(),
                'approved_by' => $status === 'approved' ? $this->uid() : null,
                'approved_at' => $status === 'approved' ? now()->subDay() : null,
            ]);
            $ln = 10;
            foreach ($qtyMap as $code => $qty) {
                $item = $items[$code] ?? null;
                if (!$item) {
                    continue;
                }
                $req->lines()->create([
                    'line_number' => $ln,
                    'item_id' => $item->id,
                    'quantity' => $qty,
                    'required_date' => now()->addDays(15)->toDateString(),
                    'supplier_id' => $supplier?->id,
                ]);
                $ln += 10;
            }
        }
    }

    private function createPurchaseReceipts(): void
    {
        if ($this->skipIf(Receipt::class)) {
            return;
        }

        $orders = PurchaseOrder::whereIn('status', ['approved', 'partially_received', 'fully_received'])->orderBy('id')->get();
        $rawWarehouse = Warehouse::where('code', 'WH-RAW')->first();
        $item = Item::where('code', 'M-001')->first();

        $statuses = ['draft', 'confirmed', 'received'];
        $i = 0;
        foreach ($orders as $order) {
            $receipt = Receipt::forceCreate([
                'receipt_no' => 'RC-2026-00' . (5001 + $i),
                'receipt_type' => 'purchase',
                'supplier_id' => $order->supplier_id,
                'purchase_order_id' => $order->id,
                'receipt_date' => now()->subDays(8 - $i)->toDateString(),
                'warehouse_id' => $rawWarehouse?->id,
                'status' => $statuses[$i % count($statuses)],
                'created_by' => $this->uid(),
                'approved_at' => $i > 0 ? now()->subDays(2) : null,
            ]);
            $receipt->lines()->create([
                'line_number' => 10,
                'item_id' => $item?->id,
                'quantity' => 300,
                'inspected_quantity' => 300,
                'qualified_quantity' => 298,
                'rejected_quantity' => 2,
                'batch_number' => 'B2026071' . ($i + 1),
                'production_date' => now()->subDays(20)->toDateString(),
                'quality_status' => 'qualified',
            ]);
            $i++;
        }
    }

    private function createPurchaseReturns(): void
    {
        if ($this->skipIf(PurchaseReturn::class)) {
            return;
        }

        $order = PurchaseOrder::where('status', 'fully_received')->first() ?? PurchaseOrder::first();
        if (!$order) {
            return;
        }
        $item = Item::where('code', 'M-002')->first();

        foreach (['draft', 'approved'] as $idx => $status) {
            $ret = PurchaseReturn::forceCreate([
                'return_no' => 'PR-2026-00' . (6001 + $idx),
                'supplier_id' => $order->supplier_id,
                'purchase_order_id' => $order->id,
                'return_date' => now()->subDays(4 - $idx)->toDateString(),
                'status' => $status,
                'total_amount' => 1250,
                'reason' => $idx === 0 ? '来料规格不符' : '来料不良退回',
                'created_by' => $this->uid(),
                'approved_at' => $status === 'approved' ? now()->subDay() : null,
            ]);
            $ret->lines()->create([
                'line_number' => 10,
                'item_id' => $item?->id,
                'quantity' => 50,
                'batch_number' => 'B20260702',
            ]);
        }
    }

    // ==================== 生产 ====================

    private function createProductionPlans(): void
    {
        if ($this->skipIf(ProductionPlan::class)) {
            return;
        }

        $workCenter = WorkCenter::where('code', 'WC-ASM')->first();
        $so = SalesOrder::where('status', 'approved')->first() ?? SalesOrder::first();

        $rows = [
            ['PP-2026-0007001', 'draft', 'F-001', 20, '2026-08-05'],
            ['PP-2026-0007002', 'approved', 'F-002', 12, '2026-08-12'],
        ];

        foreach ($rows as [$no, $status, $itemCode, $qty, $date]) {
            $item = Item::where('code', $itemCode)->first();
            if (!$item) {
                continue;
            }
            $plan = ProductionPlan::forceCreate([
                'plan_no' => $no,
                'plan_date' => now()->subDays(3)->toDateString(),
                'status' => $status,
                'remark' => '月度产能计划',
                'created_by' => $this->uid(),
                'approved_by' => $status === 'approved' ? $this->uid() : null,
                'approved_at' => $status === 'approved' ? now()->subDay() : null,
            ]);
            $plan->lines()->create([
                'line_number' => 10,
                'item_id' => $item->id,
                'quantity' => $qty,
                'plan_date' => $date,
                'work_center_id' => $workCenter?->id,
                'sales_order_id' => $so?->id,
            ]);
        }
    }

    private function createMaterialIssues(): void
    {
        if ($this->skipIf(MaterialIssue::class)) {
            return;
        }

        $orders = ProductionOrder::whereIn('status', ['released', 'in_progress'])->orderBy('id')->get();
        $rawWarehouse = Warehouse::where('code', 'WH-RAW')->first();
        $sfWarehouse = Warehouse::where('code', 'WH-SF')->first();
        $items = Item::whereIn('code', ['M-001', 'S-001'])->get()->keyBy('code');

        $statuses = ['draft', 'confirmed', 'issued'];
        $i = 0;
        foreach ($orders as $order) {
            $issue = MaterialIssue::forceCreate([
                'issue_no' => 'MI-2026-00' . (7001 + $i),
                'issue_type' => 'production',
                'production_order_id' => $order->id,
                'issue_date' => now()->subDays(6 - $i)->toDateString(),
                'warehouse_id' => $i % 2 === 0 ? $rawWarehouse?->id : $sfWarehouse?->id,
                'status' => $statuses[$i % count($statuses)],
                'created_by' => $this->uid(),
                'approved_at' => $i > 0 ? now()->subDays(2) : null,
            ]);

            $codes = $i % 2 === 0 ? ['M-001', 'S-001'] : ['S-001'];
            $ln = 10;
            foreach ($codes as $code) {
                $item = $items[$code] ?? null;
                if (!$item) {
                    continue;
                }
                $issue->lines()->create([
                    'line_number' => $ln,
                    'item_id' => $item->id,
                    'quantity' => 40,
                    'batch_number' => 'B2026070' . ($i + 1),
                ]);
                $ln += 10;
            }
            $i++;
        }
    }

    private function createProductionReports(): void
    {
        if ($this->skipIf(ProductionReport::class)) {
            return;
        }

        $orders = ProductionOrder::whereIn('status', ['in_progress', 'completed', 'closed'])->orderBy('id')->get();

        $i = 0;
        foreach ($orders as $order) {
            ProductionReport::forceCreate([
                'report_no' => 'PR-2026-00' . (8001 + $i),
                'production_order_id' => $order->id,
                'report_date' => now()->subDays(5 - $i)->toDateString(),
                'quantity' => 5 + $i,
                'scrap_quantity' => $i % 2,
                'rework_quantity' => 0,
                'remark' => '当日产出汇报',
                'created_by' => $this->uid(),
            ]);
            $i++;
        }
    }

    private function createProductionReceipts(): void
    {
        if ($this->skipIf(ProductionReceipt::class)) {
            return;
        }

        $orders = ProductionOrder::whereIn('status', ['completed', 'closed'])->orderBy('id')->get();
        $fgWarehouse = Warehouse::where('code', 'WH-FG')->first();

        $statuses = ['draft', 'confirmed'];
        $i = 0;
        foreach ($orders as $order) {
            $receipt = ProductionReceipt::forceCreate([
                'receipt_no' => 'FR-2026-00' . (9001 + $i),
                'production_order_id' => $order->id,
                'receipt_date' => now()->subDays(4 - $i)->toDateString(),
                'warehouse_id' => $fgWarehouse?->id,
                'status' => $statuses[$i % count($statuses)],
                'created_by' => $this->uid(),
                'approved_at' => $i > 0 ? now()->subDay() : null,
            ]);
            $receipt->lines()->create([
                'line_number' => 10,
                'item_id' => $order->item_id,
                'quantity' => 10,
                'batch_number' => 'B2026070' . ($i + 1),
                'production_date' => now()->subDays(4)->toDateString(),
                'quality_status' => 'qualified',
            ]);
            $i++;
        }
    }

    // ==================== 质量 ====================

    private function createQualityExtras(): void
    {
        if ($this->skipIf(DefectCode::class)) {
            return;
        }

        $codes = [
            ['DF-SURF', '表面划伤', '外观', '表面存在划痕/压痕'],
            ['DF-DIM', '尺寸超差', '尺寸', '关键尺寸超出公差'],
            ['DF-FUNC', '功能失效', '功能', '通电后功能异常'],
            ['DF-PKG', '包装破损', '包装', '外包装破损或受潮'],
        ];
        $defectCodes = [];
        foreach ($codes as $row) {
            $defectCodes[$row[0]] = DefectCode::forceCreate([
                'code' => $row[0],
                'name' => $row[1],
                'category' => $row[2],
                'description' => $row[3],
                'active' => true,
                'created_by' => $this->uid(),
            ]);
        }

        $inspections = QualityInspection::orderBy('id')->take(3)->get();
        $i = 0;
        foreach ($inspections as $inspection) {
            $code = collect($defectCodes)->values()[$i % count($defectCodes)];
            DefectRecord::forceCreate([
                'record_no' => 'DR-2026-00' . (3001 + $i),
                'defect_type' => $inspection->inspection_type,
                'defect_code_id' => $code->id,
                'production_order_id' => ProductionOrder::value('id'),
                'item_id' => $inspection->item_id,
                'batch_number' => $inspection->batch_number,
                'quantity' => 1 + $i,
                'disposition' => $i % 2 === 0 ? 'rework' : 'reject',
                'description' => $code->description,
                'root_cause' => '来料/加工过程波动',
                'corrective_action' => '调整工艺参数并复检',
                'created_by' => $this->uid(),
            ]);
            $i++;
        }
    }

    // ==================== 财务 ====================

    private function createFinance(): void
    {
        if ($this->skipIf(CustomerInvoice::class)) {
            return;
        }

        $orders = SalesOrder::whereIn('status', ['approved', 'partially_delivered', 'fully_delivered', 'closed'])->orderBy('id')->take(3)->get();
        $i = 0;
        $invoices = [];
        foreach ($orders as $order) {
            $subtotal = (float) $order->total_amount;
            $tax = round($subtotal * 0.13, 2);
            $total = $subtotal + $tax;
            $status = $i === 0 ? 'paid' : ($i === 1 ? 'partial' : 'unpaid');
            $paid = $status === 'paid' ? $total : ($status === 'partial' ? round($total / 2, 2) : 0);

            $invoice = CustomerInvoice::forceCreate([
                'invoice_no' => 'INV-2026-' . str_pad((string) (3001 + $i), 4, '0', STR_PAD_LEFT),
                'customer_id' => $order->customer_id,
                'sales_order_id' => $order->id,
                'invoice_date' => now()->subDays(20 - $i * 3)->toDateString(),
                'due_date' => now()->addDays(30 - $i * 5)->toDateString(),
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'paid_amount' => $paid,
                'status' => $status,
                'created_by' => $this->uid(),
            ]);
            $invoices[] = [$invoice, $paid];
            $i++;
        }

        $purchaseOrders = PurchaseOrder::whereIn('status', ['approved', 'partially_received', 'fully_received'])->orderBy('id')->take(2)->get();
        $bills = [];
        $j = 0;
        foreach ($purchaseOrders as $po) {
            $subtotal = (float) $po->total_amount;
            $tax = round($subtotal * 0.13, 2);
            $total = $subtotal + $tax;
            $status = $j === 0 ? 'paid' : 'unpaid';
            $paid = $status === 'paid' ? $total : 0;

            $bill = SupplierBill::forceCreate([
                'bill_no' => 'BILL-2026-' . str_pad((string) (5001 + $j), 4, '0', STR_PAD_LEFT),
                'supplier_id' => $po->supplier_id,
                'purchase_order_id' => $po->id,
                'bill_date' => now()->subDays(18 - $j * 4)->toDateString(),
                'due_date' => now()->addDays(25)->toDateString(),
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'paid_amount' => $paid,
                'status' => $status,
                'created_by' => $this->uid(),
            ]);
            $bills[] = [$bill, $paid];
            $j++;
        }

        $k = 0;
        foreach ($invoices as [$invoice, $paid]) {
            if ($paid <= 0) {
                continue;
            }
            Payment::forceCreate([
                'payment_no' => 'RC-2026-' . str_pad((string) (7001 + $k), 4, '0', STR_PAD_LEFT),
                'type' => 'receipt',
                'customer_id' => $invoice->customer_id,
                'customer_invoice_id' => $invoice->id,
                'payment_date' => now()->subDays(5)->toDateString(),
                'amount' => $paid,
                'method' => 'bank_transfer',
                'reference_no' => 'BANK2026' . (1000 + $k),
                'created_by' => $this->uid(),
            ]);
            $k++;
        }

        foreach ($bills as [$bill, $paid]) {
            if ($paid <= 0) {
                continue;
            }
            Payment::forceCreate([
                'payment_no' => 'FK-2026-' . str_pad((string) (7501), 4, '0', STR_PAD_LEFT),
                'type' => 'payment',
                'supplier_id' => $bill->supplier_id,
                'supplier_bill_id' => $bill->id,
                'payment_date' => now()->subDays(3)->toDateString(),
                'amount' => $paid,
                'method' => 'bank_transfer',
                'reference_no' => 'BANK20262001',
                'created_by' => $this->uid(),
            ]);
        }
    }

    // ==================== CRM ====================

    private function createCrm(): void
    {
        if ($this->skipIf(Lead::class)) {
            return;
        }

        $leads = [
            ['李明', '李经理', '13811110001', 'liming@lead.com', '常州新达机械有限公司', 'exhibition', 'qualified', null],
            ['王芳', '王主管', '13811110002', 'wangfang@lead.com', '郑州恒信重工', 'website', 'contacted', null],
            ['张伟', '张总', '13811110003', 'zhangwei@lead.com', '青岛海通自动化', 'referral', 'new', null],
            ['赵强', '赵工', '13811110004', 'zhaoqiang@lead.com', '天津科创装备', 'cold_call', 'opportunity', '已确认样机需求'],
            ['孙丽', '孙经理', '13811110005', 'sunli@lead.com', '苏州精锐机电', 'manual', 'converted', null],
        ];

        $leadIds = [];
        foreach ($leads as [$name, $contact, $phone, $email, $company, $source, $status, $remark]) {
            $lead = Lead::forceCreate([
                'name' => $company,
                'contact_person' => $contact,
                'phone' => $phone,
                'email' => $email,
                'company' => $company,
                'source' => $source,
                'status' => $status,
                'remark' => $remark ?: "{$contact}（{$name}）咨询传送带设备",
                'assigned_to' => $this->uid(),
                'created_by' => $this->uid(),
            ]);
            $leadIds[] = $lead->id;
        }

        $opportunities = [
            ['CT-200 采购项目', 0, 'prospecting', 180000, 30, '2026-09-30'],
            ['AS-100 大批量采购', 1, 'qualification', 320000, 50, '2026-10-15'],
            ['车间自动化改造', 3, 'negotiation', 560000, 75, '2026-08-20'],
            ['备品备件年度订单', 2, 'prospecting', 45000, 20, '2026-11-30'],
        ];

        $oppIds = [];
        foreach ($opportunities as [$name, $leadIdx, $stage, $amount, $probability, $closeDate]) {
            $opp = Opportunity::forceCreate([
                'name' => $name,
                'lead_id' => $leadIds[$leadIdx] ?? null,
                'expected_amount' => $amount,
                'probability' => $probability,
                'expected_close_date' => $closeDate,
                'stage' => $stage,
                'remark' => '客户预算与交期已初步确认',
                'assigned_to' => $this->uid(),
                'created_by' => $this->uid(),
            ]);
            $oppIds[] = $opp->id;
        }

        $followUps = [
            [0, 'call', '首次电话沟通，客户对传送带参数有疑问，已发送产品彩页'],
            [1, 'email', '发送报价单，等待客户内部评审'],
            [1, 'meeting', '到厂参观，认可焊接工艺水平'],
            [2, 'note', '网络表单留资，意向度一般，需持续跟进'],
            [3, 'call', '确认样机验收时间，安排技术交流'],
            [3, 'meeting', '技术交流完成，进入商务谈判'],
        ];
        foreach ($followUps as $i => [$leadIdx, $type, $content]) {
            FollowUp::forceCreate([
                'lead_id' => $leadIds[$leadIdx] ?? null,
                'opportunity_id' => $i >= 3 ? ($oppIds[$leadIdx - 3] ?? null) : null,
                'type' => $type,
                'content' => $content,
                'created_by' => $this->uid(),
            ]);
        }
    }

    // ==================== 审批流 ====================

    private function createApprovals(): void
    {
        if ($this->skipIf(ApprovalFlow::class)) {
            return;
        }

        $flows = [
            ['销售订单二级审批', 'sales_order', '销售经理审批 → 总经理审批', [
                ['销售经理审批', 'role', 'sales_manager'],
                ['总经理审批', 'role', 'admin'],
            ]],
            ['采购订单三级审批', 'purchase_order', '采购 → 财务 → 总经理', [
                ['采购经理审批', 'role', 'purchase_manager'],
                ['财务审核', 'role', 'accountant'],
                ['总经理审批', 'role', 'admin'],
            ]],
        ];

        foreach ($flows as [$name, $docType, $remark, $steps]) {
            $flow = ApprovalFlow::forceCreate([
                'name' => $name,
                'document_type' => $docType,
                'enabled' => true,
            ]);
            foreach ($steps as $i => [$stepName, $approverType, $approverValue]) {
                $flow->steps()->create([
                    'step_order' => ($i + 1) * 10,
                    'name' => $stepName,
                    'approver_type' => $approverType,
                    'approver_value' => $approverValue,
                ]);
            }
        }

        $salesUser = User::where('email', 'sales@demo.com')->first();
        $adminUser = $this->admin;
        $salesOrder = SalesOrder::where('status', 'submitted')->first() ?? SalesOrder::first();
        if ($salesOrder && $salesUser && $adminUser) {
            ApprovalTask::forceCreate([
                'document_type' => 'sales_order',
                'document_id' => $salesOrder->id,
                'step_order' => 10,
                'step_name' => '销售经理审批',
                'approver_id' => $salesUser->id,
                'status' => 'pending',
            ]);
            ApprovalTask::forceCreate([
                'document_type' => 'sales_order',
                'document_id' => $salesOrder->id,
                'step_order' => 20,
                'step_name' => '总经理审批',
                'approver_id' => $adminUser->id,
                'status' => 'pending',
            ]);
        }
    }

    // ==================== HR ====================

    private function createHr(): void
    {
        if ($this->skipIf(Employee::class)) {
            return;
        }

        $users = User::orderBy('id')->get()->keyBy('email');
        $rows = [
            ['E001', '张总经理', '总经办', '总经理', 13800000001, 'boss@demo.com'],
            ['E002', '张销售', '销售部', '销售经理', 13800000002, 'sales@demo.com'],
            ['E003', '李采购', '采购部', '采购经理', 13800000003, 'purchase@demo.com'],
            ['E004', '王生产', '生产部', '生产主管', 13800000004, 'production@demo.com'],
            ['E005', '赵仓储', '仓储部', '仓储主管', 13800000005, 'warehouse@demo.com'],
            ['E006', '孙质检', '质量部', '质量工程师', 13800000006, 'quality@demo.com'],
            ['E007', '周财务', '财务部', '会计', 13800000007, 'accounting@demo.com'],
            ['E008', '吴查看', '总经办', '行政专员', 13800000008, 'viewer@demo.com'],
        ];

        $employees = [];
        foreach ($rows as [$no, $name, $dept, $position, $phone, $email]) {
            $employees[] = Employee::forceCreate([
                'emp_no' => $no,
                'name' => $name,
                'department' => $dept,
                'position' => $position,
                'phone' => (string) $phone,
                'email' => $email,
                'hire_date' => '2024-03-01',
                'status' => 'active',
                'user_id' => $users[$email]->id ?? null,
            ]);
        }

        $statuses = ['present', 'late', 'absent', 'leave', 'present'];
        foreach ($employees as $i => $employee) {
            foreach (range(1, 5) as $day) {
                Attendance::forceCreate([
                    'employee_id' => $employee->id,
                    'date' => now()->subDays($day)->toDateString(),
                    'check_in' => $statuses[$i % count($statuses)] === 'absent' ? null : sprintf('%02d:%02d:00', 8 + ($i % 2), $i * 3 % 60),
                    'check_out' => $statuses[$i % count($statuses)] === 'absent' ? null : sprintf('%02d:%02d:00', 17 + ($i % 2), $i * 5 % 60),
                    'status' => $statuses[($i + $day) % count($statuses)],
                    'remark' => $day === 1 ? '演示数据' : null,
                ]);
            }
        }
    }

    // ==================== 项目 ====================

    private function createProjects(): void
    {
        if ($this->skipIf(Project::class)) {
            return;
        }

        $customers = Customer::orderBy('id')->take(2)->get();
        $projects = [
            ['2026 年度产线升级项目', '对装配线进行自动化升级，提升产能 30%', 'in_progress', '2026-01-05', '2026-12-31', 850000],
            ['车间 MES 试点项目', '试点车间 MES 系统上线', 'planning', '2026-07-01', '2026-12-31', 320000],
        ];

        $projectIds = [];
        $i = 0;
        foreach ($projects as [$name, $desc, $status, $start, $end, $budget]) {
            $project = Project::forceCreate([
                'name' => $name,
                'description' => $desc,
                'status' => $status,
                'start_date' => $start,
                'end_date' => $end,
                'budget' => $budget,
                'manager_id' => $this->uid(),
                'customer_id' => $customers[$i]->id ?? null,
            ]);
            $projectIds[] = $project->id;
            $i++;
        }

        $tasks = [
            [0, '需求调研与方案确认', 'completed', 1, '2026-02-20'],
            [0, '设备选型与采购', 'in_progress', 1, '2026-08-30'],
            [0, '产线安装调试', 'pending', 2, '2026-10-31'],
            [1, '试点车间流程梳理', 'in_progress', 2, '2026-08-15'],
            [1, 'MES 系统部署', 'pending', 3, '2026-11-30'],
        ];

        $taskIds = [];
        foreach ($tasks as [$pIdx, $name, $status, $priority, $due]) {
            $task = ProjectTask::forceCreate([
                'project_id' => $projectIds[$pIdx] ?? null,
                'name' => $name,
                'description' => $name . '相关工作',
                'status' => $status,
                'priority' => $priority,
                'due_date' => $due,
                'assignee_id' => $this->uid(),
            ]);
            $taskIds[] = $task->id;
        }

        foreach ($taskIds as $i => $taskId) {
            TimeEntry::forceCreate([
                'project_task_id' => $taskId,
                'user_id' => $this->uid(),
                'date' => now()->subDays(3 + $i)->toDateString(),
                'hours' => 4 + $i,
                'description' => '当日工时登记',
            ]);
        }
    }

    // ==================== 资产 ====================

    private function createAssets(): void
    {
        if ($this->skipIf(Asset::class)) {
            return;
        }

        $rows = [
            ['FA-2024-0001', 'CNC 数控切割机', '生产设备', 'A栋车间', 480000, 10, 'in_use'],
            ['FA-2024-0002', '工业机器人 6 轴', '生产设备', '总装车间', 320000, 8, 'in_use'],
            ['FA-2025-0003', '叉车 3 吨', '运输设备', '仓储区', 128000, 6, 'in_use'],
            ['FA-2025-0004', 'ERP 服务器', 'IT 设备', '机房', 96000, 5, 'in_use'],
            ['FA-2023-0005', '老式冲床', '生产设备', 'B栋车间', 75000, 12, 'maintenance'],
        ];

        foreach ($rows as $i => [$no, $name, $category, $location, $cost, $years, $status]) {
            Asset::forceCreate([
                'asset_no' => $no,
                'name' => $name,
                'category' => $category,
                'location' => $location,
                'original_cost' => $cost,
                'useful_life_years' => $years,
                'purchase_date' => now()->subYears(2 + $i)->toDateString(),
                'start_depreciation_date' => now()->subYears(2 + $i)->toDateString(),
                'status' => $status,
                'assigned_to' => $this->uid(),
            ]);
        }
    }

    // ==================== 电商 ====================

    private function createEcommerce(): void
    {
        if ($this->skipIf(EcommerceOrder::class)) {
            return;
        }

        $rows = [
            ['taobao', 'TB20260701001', '淘宝-张先生', '13900001111', '浙江省杭州市余杭区文一西路 969 号', 125000, 'paid', [['智能传送带单元 CT-200', 1, 3500], ['瓦楞纸箱 大号', 4, 12]]],
            ['jd', 'JD20260701002', '京东-李女士', '13900002222', '北京市朝阳区建国路 88 号', 98000, 'shipped', [['自动化分拣模块 AS-100', 1, 4500]]],
            ['amazon', 'AMZ20260702003', 'Amazon-Mr.Lee', '13900003333', '上海市浦东新区张江路 100 号', 260000, 'paid', [['智能传送带单元 CT-200', 4, 3500]]],
            ['pdd', 'PDD20260703004', '拼多多-王先生', '13900004444', '成都市高新区天府大道 400 号', 66000, 'pending', [['焊接框架组件 A型', 3, 200]]],
            ['taobao', 'TB20260704005', '淘宝-陈女士', '13900005555', '广东省深圳市南山区科技园南路 200 号', 75000, 'completed', [['EPE珍珠棉内衬', 10, 8]]],
            ['jd', 'JD20260705006', '京东-赵经理', '13900006666', '江苏省苏州市工业园区苏虹路 3 号', 155000, 'refund', [['自动化分拣模块 AS-100', 2, 4500]]],
        ];

        foreach ($rows as [$platform, $no, $buyer, $phone, $address, $amount, $status, $items]) {
            EcommerceOrder::forceCreate([
                'platform' => $platform,
                'platform_order_no' => $no,
                'buyer_name' => $buyer,
                'buyer_phone' => $phone,
                'shipping_address' => $address,
                'total_amount' => $amount,
                'status' => $status,
                'items' => json_encode($items, JSON_UNESCAPED_UNICODE),
                'synced_at' => now()->subDays(2),
            ]);
        }
    }

    // ==================== 通知 / AI ====================

    private function createNotifications(): void
    {
        if ($this->skipIf(Notification::class)) {
            return;
        }

        $users = User::orderBy('id')->get();
        $templates = [
            ['order', '销售订单待审核', '销售订单 SO-2026-002002 已提交，请及时审核。', '/app/sales/orders'],
            ['inventory', '库存低于安全库存', '原材料仓库部分物料低于安全库存，请及时补货。', '/app/inventory'],
            ['quality', '来料检验不合格', '来料检验单存在不合格批次，请安排处理。', '/app/quality/inspections'],
            ['system', '系统维护通知', '系统将于本周日 02:00-04:00 进行例行维护。', '/app/dashboard'],
        ];

        foreach ($users as $user) {
            foreach (array_slice($templates, 0, $user->hasRole('admin') ? 4 : 2) as $i => [$type, $title, $message, $link]) {
                Notification::forceCreate([
                    'user_id' => $user->id,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'data' => json_encode(['demo' => true], JSON_UNESCAPED_UNICODE),
                    'link' => $link,
                    'read_at' => $i === 3 ? now()->subDay() : null,
                ]);
            }
        }
    }

    private function createAiSetting(): void
    {
        if ($this->skipIf(AiSetting::class)) {
            return;
        }

        AiSetting::forceCreate([
            'provider' => 'deepseek',
            'api_key' => '',
            'base_url' => 'https://api.deepseek.com/v1',
            'model' => 'deepseek-chat',
            'vision_enabled' => true,
            'vision_model' => 'deepseek-chat',
            'temperature' => 0.7,
            'max_tokens' => 2048,
            'enabled' => false,
        ]);
    }
}