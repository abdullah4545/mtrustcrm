<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Sale;
use App\Models\SalePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\CrmAccess;

class CrmReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:sale.view_all_branches|sale.view_branch|sale.view_self')->only(['sales','collections']);
        $this->middleware('permission:lead.view_all_branches|lead.view_branch|lead.view_self')->only(['leads']);
    }

    private function applyScope($query, string $allPermission, string $branchPermission, string $selfColumn, string $branchColumn = 'branch_id')
    {
        $u = Auth::user();
        if ($u->can($allPermission)) {
            return $query;
        }
        if ($u->can($branchPermission)) {
            return $query->where($branchColumn, $u->branch_id);
        }
        return $query->where($selfColumn, $u->id);
    }

    public function sales(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());

        $sales = $this->applyScope(Sale::query(), 'sale.view_all_branches', 'sale.view_branch', 'sold_by');
        $sales->whereBetween('sale_date', [$from, $to]);

        $summary = [
            'sales_count' => (clone $sales)->count(),
            'sales_total' => (float)(clone $sales)->sum('grand_total'),
            'paid_total' => (float)(clone $sales)->sum('paid_total'),
            'due_total' => (float)(clone $sales)->sum('due_total'),
        ];

        $rows = $sales->latest('sale_date')->paginate(30)->withQueryString();
        return view('backend.content.reports.sales', compact('summary','rows','from','to'));
    }

    public function leads(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());

        $leads = $this->applyScope(Lead::query(), 'lead.view_all_branches', 'lead.view_branch', 'assigned_user_id')
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59']);

        $summary = [
            'total' => (clone $leads)->count(),
            'open' => (clone $leads)->where('lead_state','open')->count(),
            'closed' => (clone $leads)->where('lead_state','closed')->count(),
            'value' => (float)(clone $leads)->sum('expected_value'),
        ];

        $rows = $leads->with('statusStage:id,name,color')->latest()->paginate(30)->withQueryString();
        return view('backend.content.reports.leads', compact('summary','rows','from','to'));
    }

    public function collections(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());

        $u = Auth::user();
        $payments = SalePayment::query();
        if ($u->can('sale.view_all_branches')) {
            // all branches
        } elseif ($u->can('sale.view_branch')) {
            $payments->where('branch_id', $u->branch_id);
        } else {
            $payments->whereIn('sale_id', Sale::where('sold_by', $u->id)->select('id'));
        }
        $payments->whereBetween('payment_date', [$from, $to]);

        $summary = [
            'count' => (clone $payments)->count(),
            'amount' => (float)(clone $payments)->sum('amount'),
        ];

        $rows = $payments->with('sale:id,invoice_no,client_name,client_phone')
            ->latest('payment_date')->paginate(30)->withQueryString();

        return view('backend.content.reports.collections', compact('summary','rows','from','to'));
    }
}
