<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Activity;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\Sale;
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
        $allDistrictsAllowed = false;
        if (CrmAccess::hasAreaRestriction(Auth::user())) {
            $territories = Auth::user()->areaAssignments()->with(['district:id,name','upazila:id,name'])->get()->groupBy('district_id');
            $activeDistrictCount = \App\Models\District::where('is_active',1)->count();
            $allDistrictsAllowed = $activeDistrictCount > 0 && $territories->count() === $activeDistrictCount && $territories->every(fn($rows)=>$rows->contains(fn($x)=>is_null($x->upazila_id)));
        }
        return view('backend.content.maincontent', compact('branches','territories','allDistrictsAllowed'));
    }

    private function canSeeAllBranches(): bool
    {
        return Auth::user()->can('dashboard.view_all_branches');
    }

    private function baseLeadQuery($branchId = null)
    {
        $u = Auth::user();
        $q = Lead::query();

        if ($u->can('lead.view_all_branches')) {
            if ($branchId) $q->where('branch_id', $branchId);

        } else {
            $q->where('assigned_user_id', $u->id);
        }
        return $q;
    }

    private function baseSaleQuery($branchId = null)
    {
        $u = Auth::user();
        $q = Sale::query();
        if ($u->can('sale.view_all_branches')) {
            if ($branchId) $q->where('branch_id', $branchId);

        } else {
            $q->where('sold_by', $u->id);
        }
        return $q;
    }


    private function baseActivityQuery($branchId = null)
    {
        $u = Auth::user();
        $q = Activity::query();
        if ($u->can('activity.view_all')) {
            if ($branchId) $q->where('branch_id', $branchId);

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

        $canLead = $u->can('lead.view_all_branches') || $u->can('lead.view_branch') || $u->can('lead.view_self');
        $canSale = $u->can('sale.view_all_branches') || $u->can('sale.view_branch') || $u->can('sale.view_self');
        $canActivity = $u->can('activity.view_all') || $u->can('activity.view_branch') || $u->can('activity.view_self');
        $canOrg = $u->can('org.view');

        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();
        $kpi = [];
        $latestLeads = collect();

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
        }

        if ($canActivity) {
            $activityBase = $this->baseActivityQuery($branchId);
            if (!$u->can('activity.view_all')) $activityBase->whereIn('status', ['pending','rejected']);
            $kpi['activity_today'] = (clone $activityBase)->whereDate('date', now()->toDateString())->count();
        }

        if ($canSale) {
            $saleBase = $this->baseSaleQuery($branchId);
            $kpi['sales_today'] = (float)(clone $saleBase)->whereDate('sale_date', now()->toDateString())->sum('grand_total');
            $kpi['sales_month'] = (float)(clone $saleBase)->whereBetween('sale_date',[now()->startOfMonth()->toDateString(),now()->endOfMonth()->toDateString()])->sum('grand_total');
            $kpi['due_total'] = (float)(clone $saleBase)->sum('due_total');


        }

        return response()->json([
            'status'=>true,
            'visibility'=>['lead'=>$canLead,'sale'=>$canSale,'activity'=>$canActivity,'organization'=>$canOrg],
            'kpi'=>$kpi,
            'lists'=>['latest_leads'=>$latestLeads],
        ]);
    }
}
