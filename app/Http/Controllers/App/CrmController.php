<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Crm\Lead;
use App\Models\Crm\Opportunity;
use App\Models\Crm\FollowUp;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class CrmController extends Controller
{
    protected NotificationService $notifications;

    public function __construct(NotificationService $notifications)
    {
        $this->notifications = $notifications;
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->hasAnyRole(['admin', 'sales_manager', 'sales', 'viewer'])) {
                abort(403, '无 CRM 模块访问权限');
            }
            return $next($request);
        });
    }

    // ========== 线索 ==========

    public function leads()
    {
        $leads = Lead::with('assignedTo')->latest()->paginate(20);
        return view('app.crm.leads.index', compact('leads'));
    }

    public function createLead()
    {
        return view('app.crm.leads.create');
    }

    public function storeLead(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'contact_person' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'company' => 'nullable|string',
            'source' => 'required|string',
            'remark' => 'nullable|string',
        ]);
        $validated['assigned_to'] = auth()->id();
        $validated['created_by'] = auth()->id();

        Lead::create($validated);
        $lead = Lead::latest('id')->first();
        $this->auditLog('create', 'CrmLead', $lead);
        $this->notifications->sendToRole('sales_manager', '新销售线索', "新线索：{$lead->name}（来源：{$lead->source}）", 'info', route('app.crm.leads.show', $lead));
        return redirect()->route('app.crm.leads')->with('success', '线索已创建');
    }

    public function showLead(Lead $lead)
    {
        $lead->load('followUps', 'opportunities');
        return view('app.crm.leads.show', compact('lead'));
    }

    public function addFollowUp(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'content' => 'required|string',
        ]);
        $validated['lead_id'] = $lead->id;
        $validated['created_by'] = auth()->id();
        FollowUp::create($validated);
        $this->auditLog('create', 'CrmFollowUp', FollowUp::latest('id')->first());

        return back()->with('success', '跟进记录已添加');
    }

    // ========== 商机 ==========

    public function opportunities()
    {
        $opps = Opportunity::with('lead', 'customer')->latest()->paginate(20);
        return view('app.crm.opportunities.index', compact('opps'));
    }

    public function createOpportunity()
    {
        $leads = Lead::where('status', '!=', 'converted')->get();
        $customers = \App\Models\MasterData\Customer::where('active', true)->get();
        return view('app.crm.opportunities.create', compact('leads', 'customers'));
    }

    public function storeOpportunity(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'lead_id' => 'nullable|exists:crm_leads,id',
            'customer_id' => 'nullable|exists:customers,id',
            'expected_amount' => 'required|numeric|min:0',
            'probability' => 'required|integer|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'stage' => 'required|string',
            'remark' => 'nullable|string',
        ]);
        $validated['assigned_to'] = auth()->id();
        $validated['created_by'] = auth()->id();

        Opportunity::create($validated);
        $this->auditLog('create', 'CrmOpportunity', Opportunity::latest('id')->first());
        return redirect()->route('app.crm.opportunities')->with('success', '商机已创建');
    }

    // ========== 销售漏斗看板 ==========

    public function pipeline()
    {
        $stages = [
            'prospecting' => '初步接触',
            'qualification' => '需求确认',
            'proposal' => '方案报价',
            'negotiation' => '商务谈判',
            'closed_won' => '已成交',
            'closed_lost' => '已丢失',
        ];

        $pipeline = [];
        foreach ($stages as $key => $label) {
            $opps = Opportunity::where('stage', $key)->with('lead', 'customer')->get();
            $pipeline[] = [
                'key' => $key,
                'label' => $label,
                'count' => $opps->count(),
                'value' => $opps->sum('expected_amount'),
                'opps' => $opps,
            ];
        }

        return view('app.crm.pipeline', compact('pipeline'));
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
