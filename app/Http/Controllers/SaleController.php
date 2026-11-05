<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Organization;
use App\Models\Business;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\StatusStage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\CrmAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PDF;
use Yajra\DataTables\DataTables;

class SaleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
 
        $this->middleware('permission:sale.view_all_branches|sale.view_branch|sale.view_self')
            ->only([
                'index','datatable','show',
                'create','edit',
                'createFromLead','createFromQuotation',
                'pdf','organizationOptions'
            ]);
 
        $this->middleware('permission:sale.create')
            ->only(['store']);
 
        $this->middleware('permission:sale.edit')
            ->only(['update']);
 
        $this->middleware('permission:sale.delete')
            ->only(['destroy']);
 
        $this->middleware('permission:sale.mail')
            ->only(['sendMail']);
 
        $this->middleware('permission:sale.payment.view')
            ->only([ 
            ]);
 
        $this->middleware('permission:sale.payment.add')
            ->only(['addPayment']);
    }
    private function canSeeAllBranches(): bool
    {
        return Auth::user()->can('sale.view_all_branches');
    }

    private function ensureBranchAccess(int $branchId): void
    {
        $u = Auth::user();
        if (!$this->canSeeAllBranches() && (int)$u->branch_id !== (int)$branchId) {
            abort(403);
        }
    }

    private function ensureSaleAccess(Sale $sale): void
    {
        $u = Auth::user();
        if ($u->can('sale.view_all_branches')) return;
        if ((int)$sale->sold_by === (int)$u->id) return;
        abort(403);
    }

    public function organizationOptions(Request $request)
    {
        $items = CrmAccess::applyOrganizationScope(Organization::query())
            ->when($request->filled('q'), fn($q)=>$q->where('name','like','%'.$request->input('q').'%'))
            ->orderBy('name')->paginate(25, ['id','name','phone_primary','phone_numbers','email','email_addresses']);
        return response()->json(['results'=>$items->getCollection()->map(fn($o)=>['id'=>$o->id,'text'=>$o->name,'client_phone'=>$o->phone_primary ?: ($o->phone_numbers[0] ?? ''),'client_email'=>$o->email ?: ($o->email_addresses[0] ?? '')]), 'pagination'=>['more'=>$items->hasMorePages()]]);
    }

    private function validateSaleOrganization(Request $request): void
    {
        if (!$request->filled('organization_id')) {
            $request->merge(['organization_id' => null, 'organization_contact_id' => null]);
            return;
        }
        $organization = Organization::findOrFail($request->integer('organization_id'));
        CrmAccess::ensureOrganizationAllowed($organization);
        if ($request->filled('organization_contact_id')) {
            validator($request->all(), ['organization_contact_id'=>[
                \Illuminate\Validation\Rule::exists('organization_contacts','id')->where('organization_id',$organization->id)->whereNull('deleted_at'),
            ]])->validate();
        }
    }

    private function saleNo(): string
    {
        return 'SL-' . date('ymd') . '-' . random_int(1000, 9999);
    }

    /**
     * ✅ Branch-wise Invoice No (NO GlobalSequence)
     * Pattern: BRANCHCODE-0000001
     * Concurrency-safe by locking the branch row in the same transaction.
     */
    private function generateInvoiceNoBranchWise(int $branchId, int $padding = 7): string
    {
        // 🔒 Lock branch row to serialize invoice generation per branch
        $branch = Branch::where('id', $branchId)->lockForUpdate()->firstOrFail();

        $prefix = ($branch->branch_code ?? 'BR') . '-';

        // Find last invoice for this branch with this prefix
        $last = Sale::where('branch_id', $branchId)
            ->where('invoice_no', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('invoice_no');

        $next = 1;

        if ($last) {
            // Extract numeric suffix
            $suffix = str_replace($prefix, '', $last);
            $num = (int) preg_replace('/\D/', '', $suffix);
            if ($num > 0) $next = $num + 1;
        }

        $numPadded = str_pad((string)$next, $padding, '0', STR_PAD_LEFT);
        return $prefix . $numPadded;
    }

    private function recalcPaymentStatus(Sale $sale): void
    {
        $paid = (float) $sale->opening_paid_total + (float) $sale->payments()->sum('amount');
        $grand = (float) $sale->grand_total;

        $due = max($grand - $paid, 0);

        $status = 'unpaid';
        if ($paid > 0 && $paid < $grand) $status = 'partial';
        if ($paid >= $grand && $grand > 0) $status = 'paid';

        $sale->update([
            'paid_total' => $paid,
            'due_total' => $due,
            'payment_status' => $status,
        ]);
    }

    public function index()
    {
        $statuses = StatusStage::where('status', 1)->where('is_for', 'sales')->orderBy('name')->get();
        $u = Auth::user();
        $staffs = $u->can('sale.view_all_branches') && $u->can('staff.filter')
            ? User::where('status',1)->orderBy('name')->get(['id','name'])
            : User::whereKey($u->id)->get(['id','name']);
        return view('backend.content.sales.index', compact('statuses','staffs'));
    }

    public function datatable(Request $request)
    {
        $u = Auth::user();

        $q = Sale::query()
            ->with(['statusStage:id,name,color','soldBy:id,name','organization:id,name'])
            ->latest();

        if ($u->can('sale.view_all_branches')) {
            if ($request->filled('branch_id')) $q->where('branch_id', $request->branch_id);
        } else {
            $q->where('sold_by', $u->id);
        }

        if ($request->filled('organization_id')) $q->where('organization_id', $request->integer('organization_id'));
        if ($request->filled('status_stage_id')) $q->where('status_stage_id', $request->status_stage_id);
        if ($request->filled('payment_status')) $q->where('payment_status', $request->payment_status);
        if ($u->can('sale.view_all_branches') && $u->can('staff.filter') && $request->filled('sold_by')) $q->where('sold_by', $request->integer('sold_by'));

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $q->whereBetween('sale_date', [$request->date_from, $request->date_to]);
        }

        if ($request->filled('search_text')) {
            $s = trim($request->search_text);
            $q->where(function ($qq) use ($s) {
                $qq->where('invoice_no', 'like', "%{$s}%")
                    ->orWhere('sale_no', 'like', "%{$s}%")
                    ->orWhere('client_name', 'like', "%{$s}%")
                    ->orWhere('client_phone', 'like', "%{$s}%")
                    ->orWhereHas('organization', fn($org)=>$org->where('name','like',"%{$s}%"));
            });
        }

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('invoice_display', function($row){
                $date=$row->sale_date ? Carbon::parse($row->sale_date)->timezone('Asia/Dhaka')->format('d M Y') : '-';
                return '<a class="sale-invoice" href="'.route('sales.show',$row->id).'">'.e($row->invoice_no).'</a><div class="sale-detail"><i class="feather-calendar"></i> '.e($date).'</div><div class="sale-owner"><i class="feather-user"></i> '.e($row->soldBy?->name ?? '-').'</div>';
            })
            ->addColumn('client_display', function($row){
                return '<div class="sale-org">'.e($row->organization?->name ?? 'Unknown / Legacy').'</div><div class="sale-detail"><i class="feather-user"></i> <strong>'.e($row->client_name ?: '-').'</strong></div><div class="sale-detail"><i class="feather-phone"></i> '.e($row->client_phone ?: '-').'</div>';
            })
            ->addColumn('amount_display', function($row){
                $fmt=fn($n)=>rtrim(rtrim(number_format((float)$n,2,'.',','),'0'),'.').' TK';
                return '<div class="sale-amount"><div><span>Total:</span><strong class="sale-total">'.$fmt($row->grand_total).'</strong></div><div><span>Paid:</span><strong class="sale-paid">'.$fmt($row->paid_total).'</strong></div><div><span>Due:</span><strong class="sale-due">'.$fmt($row->due_total).'</strong></div></div>';
            })
            ->addColumn('organization_name', fn($row) => $row->organization?->name ?? 'Unknown / legacy')
            ->addColumn('staff_name', fn($row) => e($row->soldBy?->name ?? '-'))
            ->editColumn('sale_date', fn($row) =>
                $row->sale_date ? Carbon::parse($row->sale_date)->timezone('Asia/Dhaka')->format('d M Y') : '-'
            )
            ->addColumn('status_badge', function ($row) {
                if (!$row->statusStage) return '-';
                return '<span style="display:inline-flex;align-items:center;gap:6px;">
                    <span style="width:10px;height:10px;border-radius:50%;background:' . $row->statusStage->color . ';"></span>
                    <span class="badge bg-light text-dark" style="border:1px solid #eee;">' . e($row->statusStage->name) . '</span>
                </span>';
            })
            ->addColumn('pay_badge', function ($row) {
                $map = [
                    'unpaid' => ['bg-danger', 'Unpaid'],
                    'partial' => ['bg-warning', 'Partial'],
                    'paid' => ['bg-success', 'Paid'],
                ];
                $m = $map[$row->payment_status] ?? ['bg-secondary', $row->payment_status];
                return '<span class="badge ' . $m[0] . '">' . $m[1] . '</span>';
            })
            ->editColumn('grand_total', fn($row) => number_format((float)$row->grand_total, 2))
            ->editColumn('due_total', fn($row) => number_format((float)$row->due_total, 2))
            ->addColumn('action', function ($row) {
                $show = route('sales.show', $row->id);
                $edit = route('sales.edit', $row->id);
                $pdf  = route('sales.pdf', $row->id);

                $html = '<div class="d-flex flex-wrap gap-1">';
                $html .= '<a class="btn btn-sm btn-secondary" href="'.$show.'"><i class="feather-eye"></i></a>';
                if (Auth::user()->can('sale.edit')) $html .= '<a class="btn btn-sm btn-primary" href="'.$edit.'"><i class="feather-edit"></i></a>';
                if (Auth::user()->can('sale.pdf')) $html .= '<a class="btn btn-sm btn-dark" href="'.$pdf.'"><i class="feather-download"></i></a>';
                if (Auth::user()->can('sale.delete')) $html .= '<button class="btn btn-sm btn-danger btn-delete" data-id="'.$row->id.'"><i class="feather-trash-2"></i></button>';
                return $html.'</div>';
            })
            ->rawColumns(['invoice_display','client_display','amount_display','status_badge', 'pay_badge', 'action'])
            ->make(true);
    }

    public function create()
    {
        $statuses = StatusStage::where('status', 1)->where('is_for', 'sales')->orderBy('name')->get();
        return view('backend.content.sales.create', [
            'statuses' => $statuses,
            'lead' => null,
            'quotation' => null,
            'prefillItems' => [],
            'taxRate' => (float)(Business::first()?->vat ?? 0),
        ]);
    }

    public function createFromLead($leadId)
    {
        $lead = Lead::with(['organization', 'organizationContact'])->findOrFail($leadId);
        $this->ensureBranchAccess((int)$lead->branch_id);
        if (Auth::user()->can('lead.view_self') && !Auth::user()->can('lead.view_branch') && !Auth::user()->can('lead.view_all_branches')) abort_unless((int)$lead->assigned_user_id === (int)Auth::id(),403);

        if ($lead->converted_sale_id) {
            return redirect()->route('sales.show', $lead->converted_sale_id)
                ->with('message', 'This lead already has a sale.');
        }

        $statuses = StatusStage::where('status', 1)->where('is_for', 'sales')->orderBy('name')->get();

        return view('backend.content.sales.create', [
            'statuses' => $statuses,
            'lead' => $lead,
            'quotation' => null,
            'prefillItems' => [],
            'taxRate' => (float)(Business::first()?->vat ?? 0),
        ]);
    }

    public function createFromQuotation($qid)
    {
        $quotation = Quotation::with(['items', 'lead', 'organization', 'organizationContact'])->findOrFail($qid);
        $this->ensureBranchAccess((int)$quotation->branch_id);
        if (Auth::user()->can('quotation.view_self') && !Auth::user()->can('quotation.view_branch') && !Auth::user()->can('quotation.view_all_branches')) abort_unless((int)$quotation->prepared_by === (int)Auth::id(),403);

        $lead = $quotation->lead;

        $statuses = StatusStage::where('status', 1)->where('is_for', 'sales')->orderBy('name')->get();

        $items = $quotation->items->map(fn($it) => [
            'product_id' => $it->product_id,
            'item_name' => $it->item_name,
            'description' => $it->description,
            'qty' => $it->qty,
            'unit' => $it->unit ?? 'pcs',
            'unit_price' => $it->unit_price,
            'tax_rate' => $it->tax_rate,
        ])->toArray();

        return view('backend.content.sales.create', [
            'statuses' => $statuses,
            'lead' => $lead,
            'quotation' => $quotation,
            'prefillItems' => $items,
            'taxRate' => (float)(Business::first()?->vat ?? 0),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'lead_id' => 'nullable|exists:leads,id',
            'quotation_id' => 'nullable|exists:quotations,id',
            'organization_id' => 'required|exists:organizations,id',
            'organization_contact_id' => 'nullable|exists:organization_contacts,id',
            'sale_date' => 'nullable|date',
            'status_stage_id' => 'nullable|exists:status_stages,id',
            'grand_total' => 'required|numeric|min:0|max:9999999999.99',
            'due_total' => 'required|numeric|min:0|lte:grand_total',
            'notes' => 'nullable|string',
        ]);

        $u = Auth::user();

        // Determine branch_id from lead/quotation if provided, else user branch
        $branchId = (int) $u->branch_id;

        $lead = null;
        $quotation = null;

        if ($request->filled('lead_id')) {
            $lead = Lead::find($request->lead_id);
            if ($lead) {
                $this->ensureBranchAccess((int)$lead->branch_id);
                if (!$u->can('lead.view_all_branches') && !$u->can('lead.view_branch')) abort_unless((int)$lead->assigned_user_id === (int)$u->id,403);
                $branchId = (int)$lead->branch_id;
            }
        }

        if ($request->filled('quotation_id')) {
            $quotation = Quotation::find($request->quotation_id);
            if ($quotation) {
                $this->ensureBranchAccess((int)$quotation->branch_id);
                if (!$u->can('quotation.view_all_branches') && !$u->can('quotation.view_branch')) abort_unless((int)$quotation->prepared_by === (int)$u->id,403);
                $branchId = (int)$quotation->branch_id;
            }
        }

        $this->validateSaleOrganization($request);

        if ($lead && $lead->converted_sale_id) {
            return redirect()->route('sales.show', $lead->converted_sale_id)
                ->with('message', 'This lead already has a sale.');
        }

        $sale = DB::transaction(function () use ($request, $branchId, $u, $lead, $quotation) {

            // ✅ Branch-wise invoice (NO GlobalSequence)
            $invoiceNo = $this->generateInvoiceNoBranchWise($branchId, 7);

            $organization = Organization::findOrFail($request->organization_id);
            $s = new Sale();
            $s->sale_no = $this->saleNo();
            $s->invoice_no = $invoiceNo;

            $s->branch_id = $branchId;

            $s->lead_id = $request->lead_id;
            $s->quotation_id = $request->quotation_id;

            $s->organization_id = $request->organization_id;
            $s->organization_contact_id = $request->organization_contact_id;

            $s->client_name = $organization->name;
            $s->client_phone = $organization->phone_primary ?: ($organization->phone_numbers[0] ?? null);
            $s->client_email = $organization->email ?: ($organization->email_addresses[0] ?? null);
            $s->client_address = null;

            $s->sold_by = $u->id;
            $s->sale_date = $request->sale_date ?? now()->toDateString();

            $s->status_stage_id = $request->status_stage_id;
            $s->notes = $request->notes;
            $s->sub_total = round((float)$request->grand_total, 2);
            $s->grand_total = $s->sub_total;
            $s->discount_amount = 0;
            $s->tax_enabled = false;
            $s->tax_rate = 0;
            $s->tax_amount = 0;
            $s->opening_paid_total = round($s->grand_total - round((float)$request->due_total, 2), 2);
            $s->save();
            $this->recalcPaymentStatus($s);

            // update lead converted_sale_id
            if ($lead) {
                $lead->converted_sale_id = $s->id;
                $lead->save();
            } elseif ($quotation && $quotation->lead_id) {
                Lead::where('id', $quotation->lead_id)->update(['converted_sale_id' => $s->id]);
            }

            return $s;
        });

        return redirect()->route('sales.edit', $sale->id)
            ->with('message', 'Sale created successfully');
    }

    public function edit($id)
    {
        $sale = Sale::with('items')->findOrFail($id);
        $this->ensureSaleAccess($sale);

        $statuses = StatusStage::where('status', 1)->where('is_for', 'sales')->orderBy('name')->get();

        return view('backend.content.sales.edit', ['sale'=>$sale,'statuses'=>$statuses,'taxRate'=>(float)(Business::first()?->vat ?? 0)]);
    }

    public function update(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);
        $this->ensureSaleAccess($sale);
        $request->validate([
            'lead_id' => 'nullable|exists:leads,id',
            'quotation_id' => 'nullable|exists:quotations,id',
            'organization_id' => 'required|exists:organizations,id',
            'organization_contact_id' => 'nullable|exists:organization_contacts,id',
            'sale_date' => 'nullable|date',
            'status_stage_id' => 'nullable|exists:status_stages,id',
            'grand_total' => 'required|numeric|min:0|max:9999999999.99',
            'due_total' => 'required|numeric|min:0|lte:grand_total',
            'notes' => 'nullable|string',
        ]);
        $this->validateSaleOrganization($request);
        DB::transaction(function () use ($request, $id) {
            $sale = Sale::whereKey($id)->lockForUpdate()->firstOrFail();
            $recorded = round((float)$sale->payments()->sum('amount'), 2);
            $total = round((float)$request->grand_total, 2);
            $due = round((float)$request->due_total, 2);
            $paid = round($total - $due, 2);
            if ($paid < $recorded) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'due_total' => 'Total minus due cannot be less than recorded payments ('.number_format($recorded, 2).').',
                ]);
            }
            $organization = Organization::findOrFail($request->organization_id);
            $sale->update([
                'organization_id' => $organization->id,
                'organization_contact_id' => $request->organization_contact_id,
                'client_name' => $organization->name,
                'client_phone' => $organization->phone_primary ?: ($organization->phone_numbers[0] ?? null),
                'client_email' => $organization->email ?: ($organization->email_addresses[0] ?? null),
                'sale_date' => $request->sale_date ?? $sale->sale_date,
                'status_stage_id' => $request->status_stage_id,
                'sub_total' => $total, 'grand_total' => $total,
                'tax_enabled' => false, 'tax_rate' => 0, 'tax_amount' => 0, 'discount_amount' => 0,
                'opening_paid_total' => round($paid - $recorded, 2),
                'notes' => $request->notes,
            ]);
            $sale->items()->delete();
            $this->recalcPaymentStatus($sale);
        });
        return back()->with('message', 'Sale updated successfully');
    }

    public function show($id)
    {
        $sale = Sale::with(['items', 'payments', 'statusStage'])->findOrFail($id);
        $this->ensureSaleAccess($sale);

        return view('backend.content.sales.show', compact('sale'));
    }

    public function destroy($id)
    {
        $sale = Sale::findOrFail($id);
        $this->ensureSaleAccess($sale);

        $sale->delete();
        return response()->json(['status' => true, 'message' => 'Sale deleted']);
    }

    public function addPayment(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);
        $this->ensureSaleAccess($sale);

        $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string|max:30',
            'transaction_ref' => 'nullable|string|max:120',
            'note' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $id) {
            $sale = Sale::whereKey($id)->lockForUpdate()->firstOrFail();
        $amount = (float) $request->amount;
        $due = (float) $sale->due_total;
        if ($amount > $due + 0.0001) {
            throw \Illuminate\Validation\ValidationException::withMessages(['amount' => 'Payment cannot be greater than current due amount ('.number_format($due,2).').']);
        }

        SalePayment::create([
            'sale_id' => $sale->id,
            'branch_id' => $sale->branch_id,
            'payment_date' => $request->payment_date,
            'amount' => $amount,
            'method' => $request->method,
            'transaction_ref' => $request->transaction_ref,
            'received_by' => Auth::id(),
            'note' => $request->note,
        ]);

        $this->recalcPaymentStatus($sale->fresh());

        });

        return back()->with('message', 'Payment added successfully');
    }

    public function pdf($id)
    {
        $sale = Sale::with(['items', 'statusStage'])->findOrFail($id);
        $this->ensureSaleAccess($sale);

        $business = Business::query()->first();
        $pdf = PDF::loadView('backend.content.sales.pdf', compact('sale', 'business'));
        return $pdf->download($sale->invoice_no . '.pdf');
    }

    public function sendMail(Request $request, $id)
    {
        $sale = Sale::with('items')->findOrFail($id);
        $this->ensureSaleAccess($sale);

        $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'nullable|string',
        ]);

        $business = Business::query()->first();
        $pdf = PDF::loadView('backend.content.sales.pdf', compact('sale', 'business'));

        Mail::send([], [], function ($m) use ($request, $pdf, $sale) {
            $m->to($request->to)
                ->subject($request->subject)
                ->setBody(nl2br(e($request->message ?? 'Please find the attached invoice.')), 'text/html')
                ->attachData($pdf->output(), $sale->invoice_no . '.pdf');
        });

        return back()->with('message', 'Invoice mailed successfully');
    }
}