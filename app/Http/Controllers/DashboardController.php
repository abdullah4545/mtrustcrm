<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Activity;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\Sale;
use App\Models\SalePayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\CrmAccess;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:dashboard.view')->only(['index','data']);
    }

    public function index()
    {
        $branches = [];
        if ($this->canSeeAllBranches()) {
            $branches = Branch::orderBy('branch_name')->get(['id','branch_name']);
        }
        $territories = collect();
        if (CrmAccess::isStaff()) {
            $territories = Auth::user()->areaAssignments()->with(['district:id,name','upazila:id,name'])->get()->groupBy('district_id');
        }
        return view('backend.content.maincontent', compact('branches','territories'));
    }

    private function canSeeAllBranches(): bool
    {
        return Auth::user()->can('dashboard.view_all_branches');
    }

    private function baseLeadQuery($branchId = null)
    {
        $u = Auth::user();
        $q = Lead::query();

        if (CrmAccess::isStaff($u)) {
            $q->where('assigned_user_id',$u->id);
        } elseif ($u->can('lead.view_all_branches')) {
            if ($branchId) $q->where('branch_id', $branchId);
        } elseif ($u->can('lead.view_branch')) {
            $q->where('branch_id', $u->branch_id);
        } else {
            $q->where('assigned_user_id', $u->id);
        }
        return $q;
    }

    private function baseSaleQuery($branchId = null)
    {
        $u = Auth::user();
        $q = Sale::query();
        if (CrmAccess::isStaff($u)) {
            $q->where('sold_by',$u->id);
        } elseif ($u->can('sale.view_all_branches')) {
            if ($branchId) $q->where('branch_id', $branchId);
        } elseif ($u->can('sale.view_branch')) {
            $q->where('branch_id', $u->branch_id);
        } else {
            $q->where('sold_by', $u->id);
        }
        return $q;
    }


    private function baseActivityQuery($branchId = null)
    {
        $u = Auth::user();
        $q = Activity::query();
        if (CrmAccess::isStaff($u)) {
            $q->where('created_by', $u->id);
        } elseif ($u->can('activity.view_all')) {
            if ($branchId) $q->where('branch_id', $branchId);
        } elseif ($u->can('activity.view_branch')) {
            $q->where('branch_id', $u->branch_id);
        } else {
            $q->where('created_by', $u->id);
        }
        return $q;
    }

    public function data(Request $request)
    {
        $u = Auth::user();
        $branchId = $request->get('branch_id');
        if ($branchId && !$this->canSeeAllBranches()) $branchId = null;
        $filter = $request->get('chart_filter', 'month');

        $canLead = $u->can('lead.view_all_branches') || $u->can('lead.view_branch') || $u->can('lead.view_self');
        $canSale = $u->can('sale.view_all_branches') || $u->can('sale.view_branch') || $u->can('sale.view_self');
        $canActivity = $u->can('activity.view_all') || $u->can('activity.view_branch') || $u->can('activity.view_self');
        $canOrg = $u->can('org.view');

        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();
        $kpi = [];
        $latestLeads = collect();
        $upcoming = collect();
        $labels = [];
        $chartData = [];

        if ($canOrg) {
            $kpi['organizations'] = CrmAccess::applyOrganizationScope(Organization::query())->count();
        }

        if ($canLead) {
            $leadBase = $this->baseLeadQuery($branchId);
            $kpi['leads_total'] = (clone $leadBase)->count();
            $kpi['leads_open'] = (clone $leadBase)->where('lead_state','open')->count();
            $kpi['leads_closed'] = (clone $leadBase)->where('lead_state','closed')->count();
            $kpi['followups_today'] = (clone $leadBase)->where('lead_state','open')->whereBetween('next_followup_at',[$todayStart,$todayEnd])->count();
            $kpi['followups_overdue'] = (clone $leadBase)->where('lead_state','open')->whereNotNull('next_followup_at')->where('next_followup_at','<',$todayStart)->count();
            $latestLeads = (clone $leadBase)->select('id','lead_no','person_name','person_phone','lead_state')->latest()->limit(5)->get();
            $upcoming = (clone $leadBase)->where('lead_state','open')->whereNotNull('next_followup_at')
                ->whereBetween('next_followup_at',[now()->startOfDay(),now()->addDays(7)->endOfDay()])
                ->select('id','lead_no','person_name','person_phone','next_followup_at','next_action_type')
                ->orderBy('next_followup_at')->limit(8)->get();
        }

        if ($canActivity) {
            $activityBase = $this->baseActivityQuery($branchId);
            $kpi['activity_today'] = (clone $activityBase)->whereDate('date', now()->toDateString())->count();
        }

        if ($canSale) {
            $saleBase = $this->baseSaleQuery($branchId);
            $kpi['sales_today'] = (float)(clone $saleBase)->whereDate('sale_date', now()->toDateString())->sum('grand_total');
            $kpi['sales_month'] = (float)(clone $saleBase)->whereBetween('sale_date',[now()->startOfMonth()->toDateString(),now()->endOfMonth()->toDateString()])->sum('grand_total');
            $kpi['due_total'] = (float)(clone $saleBase)->sum('due_total');

            $paymentBase = SalePayment::query();
            if (CrmAccess::isStaff($u)) {
                $paymentBase->whereIn('sale_id', Sale::where('sold_by',$u->id)->select('id'));
            } elseif (!$u->can('sale.view_all_branches')) {
                $paymentBase->where('branch_id',$u->branch_id);
            } elseif ($branchId) {
                $paymentBase->where('branch_id',$branchId);
            }
            $kpi['collection_today'] = (float)(clone $paymentBase)->whereDate('payment_date',now()->toDateString())->sum('amount');

            $saleQuery = clone $saleBase;
            if ($filter === 'year') {
                $rows=$saleQuery->selectRaw('MONTH(sale_date) as m, SUM(grand_total) as total')->whereYear('sale_date',now()->year)->groupBy('m')->orderBy('m')->get();
                foreach($rows as $r){$labels[]=Carbon::create()->month($r->m)->format('M');$chartData[]=(float)$r->total;}
            } elseif ($filter === 'date') {
                $rows=$saleQuery->selectRaw('sale_date as d, SUM(grand_total) as total')->where('sale_date','>=',now()->subDays(6)->toDateString())->groupBy('sale_date')->orderBy('sale_date')->get();
                foreach($rows as $r){$labels[]=Carbon::parse($r->d)->format('d M');$chartData[]=(float)$r->total;}
            } else {
                $rows=$saleQuery->selectRaw('sale_date as d, SUM(grand_total) as total')->where('sale_date','>=',now()->subDays(29)->toDateString())->groupBy('sale_date')->orderBy('sale_date')->get();
                foreach($rows as $r){$labels[]=Carbon::parse($r->d)->format('d M');$chartData[]=(float)$r->total;}
            }
        }

        return response()->json([
            'status'=>true,
            'visibility'=>['lead'=>$canLead,'sale'=>$canSale,'activity'=>$canActivity,'organization'=>$canOrg],
            'kpi'=>$kpi,
            'lists'=>['latest_leads'=>$latestLeads,'upcoming_followups'=>$upcoming],
            'charts'=>['sales'=>['labels'=>$labels,'data'=>$chartData]],
        ]);
    }
}
