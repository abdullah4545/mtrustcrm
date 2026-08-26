<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\Business;
use App\Models\QuotationItem;
use App\Models\Lead;
use App\Models\Product;
use App\Models\StatusStage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\CrmAccess;
use Illuminate\Support\Facades\Mail;
use PDF;
use Yajra\DataTables\DataTables;
class QuotationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
 
        $this->middleware('permission:quotation.view_all_branches|quotation.view_branch|quotation.view_self')
            ->only([
                'index','datatable','show','pdf',
                'productOptions','productDetails'
            ]);
 
        $this->middleware('permission:quotation.create')
            ->only(['create','createFromLead','store']); 
        $this->middleware('permission:quotation.edit')
            ->only(['edit','update']); 
        $this->middleware('permission:quotation.delete')
            ->only(['destroy']); 
        $this->middleware('permission:quotation.mail')
            ->only(['sendMail']); 
        $this->middleware('permission:quotation.convert_to_sale')
            ->only([]);
    }
    private function canSeeAllBranches(): bool
    {
        return Auth::user()->can('quotation.view_all_branches');
    }

    private function ensureQuotationAccess(Quotation $quotation): void
    {
        $u = Auth::user();
        if (CrmAccess::isStaff($u)) { abort_unless((int)$quotation->prepared_by === (int)$u->id,403); return; }
        if ($u->can('quotation.view_all_branches')) return;
        if ($u->can('quotation.view_branch') && (int)$quotation->branch_id === (int)$u->branch_id) return;
        if ($u->can('quotation.view_self') && (int)$quotation->prepared_by === (int)$u->id) return;
        abort(403);
    }

    private function ensureLeadAccess(Lead $lead): void
    {
        $u=Auth::user();
        if ($u->can('lead.view_all_branches')) return;
        if ($u->can('lead.view_branch') && (int)$lead->branch_id === (int)$u->branch_id) return;
        if ($u->can('lead.view_self') && (int)$lead->assigned_user_id === (int)$u->id) return;
        abort(403);
    }

    private function quotationNo(): string
    {
        return 'QT-' . date('ymd') . '-' . random_int(1000,9999);
    }

    public function index()
    {
        // later: datatable; now normal view
        return view('backend.content.quotations.index');
    }

    public function datatable(Request $request)
    {
        $u = Auth::user();

        $q = Quotation::query()
            ->with(['statusStage:id,name,color','organization:id,name'])
            ->latest();

        // permission-aware visibility
        if (CrmAccess::isStaff($u)) {
            $q->where('prepared_by',$u->id);
        } elseif ($u->can('quotation.view_all_branches')) {
            if ($request->filled('branch_id')) $q->where('branch_id', $request->branch_id);
        } elseif ($u->can('quotation.view_branch')) {
            $q->where('branch_id', $u->branch_id);
        } else {
            $q->where('prepared_by', $u->id);
        }

        // filters
        if ($request->filled('status_stage_id')) $q->where('status_stage_id', $request->status_stage_id);

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $q->whereBetween('issue_date', [$request->date_from, $request->date_to]);
        }

        if ($request->filled('search_text')) {
            $s = trim($request->search_text);
            $q->where(function($qq) use ($s){
                $qq->where('quotation_no','like',"%{$s}%")
                ->orWhere('client_name','like',"%{$s}%")
                ->orWhere('client_phone','like',"%{$s}%")
                ->orWhere('client_email','like',"%{$s}%");
            });
        }

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('org_name', fn($row) => $row->organization ? e($row->organization->name) : '-')
            ->addColumn('status_badge', function($row){
                if(!$row->statusStage) return '-';
                return '<span style="display:inline-flex;align-items:center;gap:6px;">
                    <span style="width:10px;height:10px;border-radius:50%;background:'.$row->statusStage->color.';"></span>
                    <span class="badge bg-light text-dark" style="border:1px solid #eee;">'.e($row->statusStage->name).'</span>
                </span>';
            })
            ->editColumn('issue_date', function($row){
                return $row->issue_date ? Carbon::parse($row->issue_date)->timezone('Asia/Dhaka')->format('d M Y') : '-';
            })
            ->editColumn('valid_until', function($row){
                return $row->valid_until ? Carbon::parse($row->valid_until)->timezone('Asia/Dhaka')->format('d M Y') : '-';
            })
            ->editColumn('grand_total', fn($row) => number_format((float)$row->grand_total, 2))
            ->addColumn('action', function($row){
                $show = route('quotations.show', $row->id);
                $edit = route('quotations.edit', $row->id);
                $pdf  = route('quotations.pdf',  $row->id);

                $html = '<div class="d-flex flex-wrap gap-1">';
                if (Auth::user()->can('quotation.convert_to_sale') && Auth::user()->can('sale.create')) $html .= '<a class="btn btn-sm btn-success" href="'.route('quotations.sales.create',$row->id).'"><i class="feather-shopping-cart"></i> Make Sale</a>';
                $html .= '<a class="btn btn-sm btn-secondary" href="'.$show.'"><i class="feather-eye"></i> View</a>';
                if (Auth::user()->can('quotation.edit')) $html .= '<a class="btn btn-sm btn-primary" href="'.$edit.'"><i class="feather-edit"></i> Edit</a>';
                if (Auth::user()->can('quotation.pdf')) $html .= '<a class="btn btn-sm btn-dark" href="'.$pdf.'"><i class="feather-download"></i> PDF</a>';
                if (Auth::user()->can('quotation.delete')) $html .= '<button class="btn btn-sm btn-danger btn-delete" data-id="'.$row->id.'"><i class="feather-trash-2"></i></button>';
                return $html.'</div>';
            })
            ->rawColumns(['status_badge','action'])
            ->make(true);
    }

    public function create()
    {
        $statuses = StatusStage::where('status',1)->where('is_for','quotation')->orderBy('name')->get();
        return view('backend.content.quotations.create', [
            'statuses' => $statuses,
            'lead' => null,
            'taxRate' => (float)(Business::first()?->vat ?? 0),
        ]);
    }

    public function createFromLead($leadId)
    {
        $lead = Lead::with(['organization','organizationContact'])->findOrFail($leadId);

        $u = Auth::user();
        $this->ensureLeadAccess($lead);

        $statuses = StatusStage::where('status',1)->where('is_for','quotation')->orderBy('name')->get();

        return view('backend.content.quotations.create', [
            'statuses' => $statuses,
            'lead' => $lead,
            'taxRate' => (float)(Business::first()?->vat ?? 0),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'lead_id' => 'nullable|exists:leads,id',
            'organization_id' => 'nullable|exists:organizations,id',
            'organization_contact_id' => 'nullable|exists:organization_contacts,id',

            'client_name' => 'nullable|string|max:150',
            'client_phone' => 'nullable|string|max:30',
            'client_email' => 'nullable|email|max:150',
            'client_address' => 'nullable|string|max:255',

            'issue_date' => 'nullable|date',
            'valid_until' => 'nullable|date',
            'currency' => 'required|string|max:10',
            'calculate_tax' => 'required|in:after_discount,before_discount',
            'tax_enabled' => 'nullable|boolean',
            'description' => 'nullable|string',
            'note_for_recipient' => 'nullable|string',
            'terms' => 'nullable|string',
            'require_signature' => 'nullable|boolean',

            'discount_amount' => 'nullable|numeric|min:0',
            'status_stage_id' => 'nullable|exists:status_stages,id',

            // items arrays
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.item_name' => 'required|string|max:200',
            'items.*.description' => 'nullable|string',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string|max:20',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
        ]);

        $u = Auth::user();

        // branch from lead if exists else current user branch
        $branchId = $u->branch_id;
        if($request->filled('lead_id')){
            $lead = Lead::find($request->lead_id);
            if($lead){
                $this->ensureLeadAccess($lead);
                $branchId = $lead->branch_id;
            }
        }

        if ($request->filled('organization_id')) CrmAccess::ensureOrganizationAllowed(Organization::findOrFail($request->organization_id), $u);

        $q = new Quotation();
        $q->quotation_no = $this->quotationNo();
        $q->branch_id = $branchId;

        $q->lead_id = $request->lead_id;
        $q->organization_id = $request->organization_id;
        $q->organization_contact_id = $request->organization_contact_id;

        $q->client_name = $request->client_name;
        $q->client_phone = $request->client_phone;
        $q->client_email = $request->client_email;
        $q->client_address = $request->client_address;

        $q->issue_date = $request->issue_date ?? now()->toDateString();
        $q->valid_until = $request->valid_until;

        $q->currency = $request->currency;
        $q->calculate_tax = $request->calculate_tax;
        $q->tax_enabled = $request->boolean('tax_enabled');
        $q->tax_rate = $q->tax_enabled ? (float)(Business::first()?->vat ?? 0) : 0;

        $q->description = $request->description;
        $q->note_for_recipient = $request->note_for_recipient;
        $q->terms = $request->terms;
        $q->require_signature = (bool)$request->require_signature;

        $q->discount_amount = (float)($request->discount_amount ?? 0);
        $q->status_stage_id = $request->status_stage_id;
        $q->prepared_by = $u->id;

        $q->save();

        // Items + totals
        $subTotal = 0; $taxTotal = 0;

        foreach($request->items as $it){
            $qty = (float)$it['qty'];
            $price = (float)$it['unit_price'];
            $taxRate = $q->tax_enabled ? (float)$q->tax_rate : 0;

            $lineBase = $qty * $price;
            $taxAmount = ($taxRate > 0) ? ($lineBase * $taxRate / 100) : 0;
            $lineTotal = $lineBase + $taxAmount;

            QuotationItem::create([
                'quotation_id' => $q->id,
                'product_id' => $it['product_id'] ?? null,
                'item_name' => $it['item_name'],
                'description' => $it['description'] ?? null,
                'qty' => $qty,
                'unit' => $it['unit'] ?? 'pcs',
                'unit_price' => $price,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'line_total' => $lineTotal,
            ]);

            $subTotal += $lineBase;
            $taxTotal += $taxAmount;
        }

        $taxBase = $q->calculate_tax === 'before_discount' ? $subTotal : max(0, $subTotal - (float)$q->discount_amount);
        $taxTotal = $q->tax_enabled ? ($taxBase * (float)$q->tax_rate / 100) : 0;
        $grand = max(0, $subTotal - (float)$q->discount_amount) + $taxTotal;

        $q->update([
            'sub_total' => $subTotal,
            'tax_amount' => $taxTotal,
            'grand_total' => $grand,
        ]);

        return redirect()->route('quotations.edit', $q->id)
            ->with('message', 'Quotation created successfully');
    }

    public function edit($id)
    {
        $q = Quotation::with('items')->findOrFail($id);

        $this->ensureQuotationAccess($q);

        $statuses = StatusStage::where('status',1)->where('is_for','quotation')->orderBy('name')->get();

        return view('backend.content.quotations.edit', ['q'=>$q,'statuses'=>$statuses,'taxRate'=>(float)(Business::first()?->vat ?? 0)]);
    }

    public function update(Request $request, $id)
    {
        $q = Quotation::with('items')->findOrFail($id);
        $this->ensureQuotationAccess($q);

        // same validation as store (short)
        $request->validate([
            'client_name' => 'nullable|string|max:150',
            'client_phone' => 'nullable|string|max:30',
            'client_email' => 'nullable|email|max:150',
            'client_address' => 'nullable|string|max:255',
            'issue_date' => 'nullable|date',
            'valid_until' => 'nullable|date',
            'currency' => 'required|string|max:10',
            'calculate_tax' => 'required|in:after_discount,before_discount',
            'tax_enabled' => 'nullable|boolean',
            'description' => 'nullable|string',
            'note_for_recipient' => 'nullable|string',
            'terms' => 'nullable|string',
            'require_signature' => 'nullable|boolean',
            'discount_amount' => 'nullable|numeric|min:0',
            'status_stage_id' => 'nullable|exists:status_stages,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.item_name' => 'required|string|max:200',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
        ]);

        $q->update([
            'client_name' => $request->client_name,
            'client_phone' => $request->client_phone,
            'client_email' => $request->client_email,
            'client_address' => $request->client_address,
            'issue_date' => $request->issue_date,
            'valid_until' => $request->valid_until,
            'currency' => $request->currency,
            'calculate_tax' => $request->calculate_tax,
            'tax_enabled' => $request->boolean('tax_enabled'),
            'tax_rate' => $request->boolean('tax_enabled') ? (float)(Business::first()?->vat ?? 0) : 0,
            'description' => $request->description,
            'note_for_recipient' => $request->note_for_recipient,
            'terms' => $request->terms,
            'require_signature' => (bool)$request->require_signature,
            'discount_amount' => (float)($request->discount_amount ?? 0),
            'status_stage_id' => $request->status_stage_id,
        ]);

        // replace items
        $q->items()->delete();

        $subTotal = 0; $taxTotal = 0;

        foreach($request->items as $it){
            $qty = (float)$it['qty'];
            $price = (float)$it['unit_price'];
            $taxRate = $q->tax_enabled ? (float)$q->tax_rate : 0;

            $lineBase = $qty * $price;
            $taxAmount = ($taxRate > 0) ? ($lineBase * $taxRate / 100) : 0;
            $lineTotal = $lineBase + $taxAmount;

            QuotationItem::create([
                'quotation_id' => $q->id,
                'product_id' => $it['product_id'] ?? null,
                'item_name' => $it['item_name'],
                'description' => $it['description'] ?? null,
                'qty' => $qty,
                'unit' => $it['unit'] ?? 'pcs',
                'unit_price' => $price,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'line_total' => $lineTotal,
            ]);

            $subTotal += $lineBase;
            $taxTotal += $taxAmount;
        }

        $taxBase = $q->calculate_tax === 'before_discount' ? $subTotal : max(0, $subTotal - (float)$q->discount_amount);
        $taxTotal = $q->tax_enabled ? ($taxBase * (float)$q->tax_rate / 100) : 0;
        $grand = max(0, $subTotal - (float)$q->discount_amount) + $taxTotal;

        $q->update([
            'sub_total' => $subTotal,
            'tax_amount' => $taxTotal,
            'grand_total' => $grand,
        ]);

        return back()->with('message','Quotation updated successfully');
    }

    public function show($id)
    {
        $q = Quotation::with('items','statusStage')->findOrFail($id);
        $this->ensureQuotationAccess($q);

        return view('backend.content.quotations.show', compact('q'));
    }

    public function destroy($id)
    {
        $q = Quotation::findOrFail($id);
        $this->ensureQuotationAccess($q);

        $q->delete();
        return redirect()->route('quotations.index')->with('message','Quotation deleted');
    }

    // PDF download
    public function pdf($id)
    {
        $q = Quotation::with('items','statusStage')->findOrFail($id);
        $this->ensureQuotationAccess($q);

        $business = Business::query()->first();
        $pdf = PDF::loadView('backend.content.quotations.pdf', compact('q', 'business'));
        return $pdf->download($q->quotation_no.'.pdf');
    }

    // Mail (simple)
    public function sendMail(Request $request, $id)
    {
        $q = Quotation::with('items')->findOrFail($id);
        $this->ensureQuotationAccess($q);

        $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'nullable|string',
        ]);

        $business = Business::query()->first();
        $pdf = PDF::loadView('backend.content.quotations.pdf', compact('q', 'business'));

        Mail::send([], [], function ($m) use ($request, $pdf, $q) {
            $m->to($request->to)
              ->subject($request->subject)
              ->setBody(nl2br(e($request->message ?? 'Please find the attached quotation.')), 'text/html')
              ->attachData($pdf->output(), $q->quotation_no.'.pdf');
        });

        return back()->with('message','Quotation mailed successfully');
    }

    // Product helpers
    public function productOptions(Request $request)
    {
        $q = Product::query()->where('status','active');

        if($request->filled('q')){
            $s = trim($request->q);
            $q->where('name','like',"%{$s}%")->orWhere('sku','like',"%{$s}%");
        }

        return $q->orderBy('name')->limit(50)->get(['id','name','sale_price','vat_rate','tax_rate']);
    }

    public function productDetails($id)
    {
        $p = Product::findOrFail($id);
        return response()->json(['status'=>true,'data'=>[
            'id'=>$p->id,
            'name'=>$p->name,
            'sale_price'=>$p->sale_price,
            'vat_rate'=>$p->vat_rate ?? 0,
            'tax_rate'=>$p->tax_rate ?? 0,
        ]]);
    }
}