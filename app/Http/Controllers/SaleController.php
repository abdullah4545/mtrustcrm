<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Business;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\StatusStage;
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
                'pdf'
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
        if (CrmAccess::isStaff($u)) { abort_unless((int)$sale->sold_by === (int)$u->id,403); return; }
        if ($u->can('sale.view_all_branches')) return;
        if ($u->can('sale.view_branch') && (int)$sale->branch_id === (int)$u->branch_id) return;
        if ($u->can('sale.view_self') && (int)$sale->sold_by === (int)$u->id) return;
        abort(403);
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
        $paid = (float) $sale->payments()->sum('amount');
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
        return view('backend.content.sales.index', compact('statuses'));
    }

    public function datatable(Request $request)
    {
        $u = Auth::user();

        $q = Sale::query()
            ->with(['statusStage:id,name,color'])
            ->latest();

        if (CrmAccess::isStaff($u)) {
            $q->where('sold_by',$u->id);
        } elseif ($u->can('sale.view_all_branches')) {
            if ($request->filled('branch_id')) $q->where('branch_id', $request->branch_id);
        } elseif ($u->can('sale.view_branch')) {
            $q->where('branch_id', $u->branch_id);
        } else {
            $q->where('sold_by', $u->id);
        }

        if ($request->filled('status_stage_id')) $q->where('status_stage_id', $request->status_stage_id);
        if ($request->filled('payment_status')) $q->where('payment_status', $request->payment_status);

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $q->whereBetween('sale_date', [$request->date_from, $request->date_to]);
        }

        if ($request->filled('search_text')) {
            $s = trim($request->search_text);
            $q->where(function ($qq) use ($s) {
                $qq->where('invoice_no', 'like', "%{$s}%")
                    ->orWhere('sale_no', 'like', "%{$s}%")
                    ->orWhere('client_name', 'like', "%{$s}%")
                    ->orWhere('client_phone', 'like', "%{$s}%");
            });
        }

        return DataTables::of($q)
            ->addIndexColumn()
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
            ->rawColumns(['status_badge', 'pay_badge', 'action'])
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
        if (CrmAccess::isStaff()) abort_unless((int)$lead->assigned_user_id === (int)Auth::id(),403);

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
        if (CrmAccess::isStaff()) abort_unless((int)$quotation->prepared_by === (int)Auth::id(),403);

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

            'organization_id' => 'nullable|exists:organizations,id',
            'organization_contact_id' => 'nullable|exists:organization_contacts,id',

            'client_name' => 'nullable|string|max:150',
            'client_phone' => 'nullable|string|max:30',
            'client_email' => 'nullable|email|max:150',
            'client_address' => 'nullable|string|max:255',

            'sale_date' => 'nullable|date',
            'status_stage_id' => 'nullable|exists:status_stages,id',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_enabled' => 'nullable|boolean',
            'notes' => 'nullable|string',

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

        // Determine branch_id from lead/quotation if provided, else user branch
        $branchId = (int) $u->branch_id;

        $lead = null;
        $quotation = null;

        if ($request->filled('lead_id')) {
            $lead = Lead::find($request->lead_id);
            if ($lead) {
                $this->ensureBranchAccess((int)$lead->branch_id);
                if (CrmAccess::isStaff($u)) abort_unless((int)$lead->assigned_user_id === (int)$u->id,403);
                $branchId = (int)$lead->branch_id;
            }
        }

        if ($request->filled('quotation_id')) {
            $quotation = Quotation::find($request->quotation_id);
            if ($quotation) {
                $this->ensureBranchAccess((int)$quotation->branch_id);
                if (CrmAccess::isStaff($u)) abort_unless((int)$quotation->prepared_by === (int)$u->id,403);
                $branchId = (int)$quotation->branch_id;
            }
        }

        if ($request->filled('organization_id')) CrmAccess::ensureOrganizationAllowed(Organization::findOrFail($request->organization_id), $u);

        if ($lead && $lead->converted_sale_id) {
            return redirect()->route('sales.show', $lead->converted_sale_id)
                ->with('message', 'This lead already has a sale.');
        }

        $sale = DB::transaction(function () use ($request, $branchId, $u, $lead, $quotation) {

            // ✅ Branch-wise invoice (NO GlobalSequence)
            $invoiceNo = $this->generateInvoiceNoBranchWise($branchId, 7);

            $s = new Sale();
            $s->sale_no = $this->saleNo();
            $s->invoice_no = $invoiceNo;

            $s->branch_id = $branchId;

            $s->lead_id = $request->lead_id;
            $s->quotation_id = $request->quotation_id;

            $s->organization_id = $request->organization_id;
            $s->organization_contact_id = $request->organization_contact_id;

            $s->client_name = $request->client_name;
            $s->client_phone = $request->client_phone;
            $s->client_email = $request->client_email;
            $s->client_address = $request->client_address;

            $s->sold_by = $u->id;
            $s->sale_date = $request->sale_date ?? now()->toDateString();

            $s->status_stage_id = $request->status_stage_id;
            $s->discount_amount = (float)($request->discount_amount ?? 0);
            $s->tax_enabled = $request->boolean('tax_enabled');
            $s->tax_rate = $s->tax_enabled ? (float)(Business::first()?->vat ?? 0) : 0;
            $s->notes = $request->notes;

            $s->save();

            // items + totals
            $subTotal = 0;
            $taxTotal = 0;

            foreach ($request->items as $it) {
                $qty = (float)$it['qty'];
                $price = (float)$it['unit_price'];
                $taxRate = $s->tax_enabled ? (float)$s->tax_rate : 0;

                $lineBase = $qty * $price;
                $taxAmount = $taxRate > 0 ? ($lineBase * $taxRate / 100) : 0;
                $lineTotal = $lineBase + $taxAmount;

                $purchaseSnap = null;
                if (!empty($it['product_id'])) {
                    $p = Product::find($it['product_id']);
                    $purchaseSnap = $p?->purchase_price;
                }

                SaleItem::create([
                    'sale_id' => $s->id,
                    'product_id' => $it['product_id'] ?? null,
                    'item_name' => $it['item_name'],
                    'description' => $it['description'] ?? null,
                    'qty' => $qty,
                    'unit' => $it['unit'] ?? 'pcs',
                    'unit_price' => $price,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'line_total' => $lineTotal,
                    'purchase_price_snapshot' => $purchaseSnap,
                ]);

                $subTotal += $lineBase;
                $taxTotal += $taxAmount;
            }

            $taxTotal = $s->tax_enabled ? ($subTotal * (float)$s->tax_rate / 100) : 0;
            $grand = max(0, $subTotal - (float)$s->discount_amount) + $taxTotal;

            $s->update([
                'sub_total' => $subTotal,
                'tax_amount' => $taxTotal,
                'grand_total' => $grand,
                'paid_total' => 0,
                'due_total' => $grand,
                'payment_status' => 'unpaid',
            ]);

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
        $sale = Sale::with('items')->findOrFail($id);
        $this->ensureSaleAccess($sale);

        $request->validate([
            'client_name' => 'nullable|string|max:150',
            'client_phone' => 'nullable|string|max:30',
            'client_email' => 'nullable|email|max:150',
            'client_address' => 'nullable|string|max:255',

            'sale_date' => 'nullable|date',
            'status_stage_id' => 'nullable|exists:status_stages,id',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_enabled' => 'nullable|boolean',
            'notes' => 'nullable|string',

            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.item_name' => 'required|string|max:200',
            'items.*.description' => 'nullable|string',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string|max:20',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
        ]);

        $sale->update([
            'client_name' => $request->client_name,
            'client_phone' => $request->client_phone,
            'client_email' => $request->client_email,
            'client_address' => $request->client_address,
            'sale_date' => $request->sale_date,
            'status_stage_id' => $request->status_stage_id,
            'discount_amount' => (float)($request->discount_amount ?? 0),
            'tax_enabled' => $request->boolean('tax_enabled'),
            'tax_rate' => $request->boolean('tax_enabled') ? (float)(Business::first()?->vat ?? 0) : 0,
            'notes' => $request->notes,
        ]);

        $sale->items()->delete();

        $subTotal = 0;
        $taxTotal = 0;

        foreach ($request->items as $it) {
            $qty = (float)$it['qty'];
            $price = (float)$it['unit_price'];
            $taxRate = $sale->tax_enabled ? (float)$sale->tax_rate : 0;

            $lineBase = $qty * $price;
            $taxAmount = $taxRate > 0 ? ($lineBase * $taxRate / 100) : 0;
            $lineTotal = $lineBase + $taxAmount;

            $purchaseSnap = null;
            if (!empty($it['product_id'])) {
                $p = Product::find($it['product_id']);
                $purchaseSnap = $p?->purchase_price;
            }

            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $it['product_id'] ?? null,
                'item_name' => $it['item_name'],
                'description' => $it['description'] ?? null,
                'qty' => $qty,
                'unit' => $it['unit'] ?? 'pcs',
                'unit_price' => $price,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'line_total' => $lineTotal,
                'purchase_price_snapshot' => $purchaseSnap,
            ]);

            $subTotal += $lineBase;
            $taxTotal += $taxAmount;
        }

        $taxTotal = $sale->tax_enabled ? ($subTotal * (float)$sale->tax_rate / 100) : 0;
        $grand = max(0, $subTotal - (float)$sale->discount_amount) + $taxTotal;

        $sale->update([
            'sub_total' => $subTotal,
            'tax_amount' => $taxTotal,
            'grand_total' => $grand,
        ]);

        $this->recalcPaymentStatus($sale->fresh());

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

        $amount = (float) $request->amount;
        $due = (float) $sale->due_total;
        if ($amount > $due + 0.0001) {
            return back()->withErrors(['amount' => 'Payment cannot be greater than current due amount ('.number_format($due,2).').'])->withInput();
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