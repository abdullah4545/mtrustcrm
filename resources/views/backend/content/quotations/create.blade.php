@extends('backend.master')

@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }} - Create Quotation
@endsection

@section('maincontent')
<div class="nxl-content">

    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Quotation</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item">CRM</li>
                <li class="breadcrumb-item"><a href="{{ route('quotations.index') }}">Quotations</a></li>
                <li class="breadcrumb-item">Create</li>
            </ul>
        </div>

        <div class="page-header-right ms-auto">
            <a href="{{ route('quotations.index') }}" class="btn btn-light-brand">Back</a>
        </div>
    </div>

    @if(session('message'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="main-content">
        <div class="card">
            <div class="card-body">

                <form method="POST" action="{{ route('quotations.store') }}" id="quotationForm">
                    @csrf

                    {{-- Lead snapshot --}}
                    <input type="hidden" name="lead_id" value="{{ $lead?->id }}">
                    <input type="hidden" name="organization_id" value="{{ $lead?->organization_id }}">
                    <input type="hidden" name="organization_contact_id" value="{{ $lead?->organization_contact_id }}">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Lead Contacts</label>
                            <input class="form-control" value="{{ $lead?->organization?->name ?? '-' }}" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Deal</label>
                            <input class="form-control" name="client_name" value="{{ $lead?->person_name }}" placeholder="Client name">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Valid Till</label>
                            <input type="date" class="form-control" name="valid_until" value="">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Client Phone</label>
                            <input class="form-control" name="client_phone" value="{{ $lead?->person_phone }}" placeholder="Phone">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Client Email</label>
                            <input class="form-control" name="client_email" value="{{ $lead?->person_email }}" placeholder="Email">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Issue Date</label>
                            <input type="date" class="form-control" name="issue_date" value="{{ now()->toDateString() }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Currency</label>
                            <select class="form-control" name="currency">
                                <option value="BDT">BDT (৳)</option>
                                <option value="USD">USD ($)</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Calculate Tax</label>
                            <select class="form-control" name="calculate_tax">
                                <option value="after_discount">After Discount</option>
                                <option value="before_discount">Before Discount</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label d-block">VAT / Tax</label>
                            <div class="form-check mt-2">
                                <input type="hidden" name="tax_enabled" value="0">
                                <input class="form-check-input" type="checkbox" name="tax_enabled" id="tax_enabled" value="1" >
                                <label class="form-check-label" for="tax_enabled">Include VAT / Tax ({{ number_format($taxRate,2) }}%)</label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status_stage_id">
                                <option value="">--</option>
                                @foreach($statuses as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="4" placeholder="Write quotation details..."></textarea>
                        </div>

                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" name="require_signature" id="require_signature">
                                <label class="form-check-label" for="require_signature">
                                    Require customer signature for approval
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Items --}}
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Items</h6>
                        <button type="button" class="btn btn-sm btn-primary" id="btnAddRow">
                            <i class="feather-plus"></i> Add Item
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="itemsTable">
                            <thead>
                                <tr>
                                    <th width="240">Product/Custom Item</th>
                                    <th>Description</th>
                                    <th width="90">Qty</th>
                                    <th width="90">Unit</th>
                                    <th width="120">Unit Price</th>
                                    <th width="90">Tax %</th>
                                    <th width="130">Amount</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- one default row --}}
                            </tbody>
                        </table>
                    </div>

                    {{-- Totals --}}
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Note for recipient</label>
                            <textarea class="form-control" name="note_for_recipient" rows="3" placeholder="e.g. Thank you for your business"></textarea>

                            <label class="form-label mt-3">Terms & Conditions</label>
                            <textarea class="form-control" name="terms" rows="3" placeholder="Terms..."></textarea>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Sub Total</span>
                                    <b id="subTotalText">0.00</b>
                                </div>

                                <div class="d-flex justify-content-between mb-2 align-items-center">
                                    <span>Discount</span>
                                    <input type="number" step="0.01" class="form-control" name="discount_amount" id="discount_amount" value="0" style="width:160px;">
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tax</span>
                                    <b id="taxText">0.00</b>
                                </div>

                                <hr>

                                <div class="d-flex justify-content-between">
                                    <span>Total</span>
                                    <b id="grandTotalText">0.00</b>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button class="btn btn-primary">Save</button>
                        <a href="{{ route('quotations.index') }}" class="btn btn-light-brand">Cancel</a>
                    </div>

                </form>

            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const PRODUCT_OPTIONS_URL = "{{ route('products.options') }}";
const GLOBAL_TAX_RATE = Number(@json($taxRate ?? 0));
const PRODUCT_DETAILS_URL = "{{ url('products') }}"; // /{id}/details

let rowIndex = 0;

document.addEventListener('DOMContentLoaded', () => {
    addRow();

    document.getElementById('btnAddRow').addEventListener('click', addRow);
    document.getElementById('discount_amount').addEventListener('input', calcTotals);
document.getElementById('tax_enabled')?.addEventListener('change', calcTotals);
document.querySelector('[name=calculate_tax]')?.addEventListener('change', calcTotals);

    // submit: ensure items[] names exist (already done)
});

function addRow(){
    const tbody = document.querySelector('#itemsTable tbody');

    const idx = rowIndex++;
    const tr = document.createElement('tr');
    tr.dataset.idx = idx;

    tr.innerHTML = `
        <td>
            <select class="form-control product_id" name="items[${idx}][product_id]">
                <option value="">-- Custom Item --</option>
            </select>
            <input class="form-control mt-2 item_name" name="items[${idx}][item_name]" placeholder="Item name *" required>
        </td>
        <td>
            <textarea class="form-control item_desc" name="items[${idx}][description]" rows="2" placeholder="Description (optional)"></textarea>
        </td>
        <td><input type="number" step="0.01" class="form-control qty" name="items[${idx}][qty]" value="1"></td>
        <td><input type="text" class="form-control unit" name="items[${idx}][unit]" value="pcs"></td>
        <td><input type="number" step="0.01" class="form-control unit_price" name="items[${idx}][unit_price]" value="0"></td>
        <td><input type="number" step="0.01" class="form-control tax_rate" name="items[${idx}][tax_rate]" value="0"></td>
        <td><b class="line_total_text">0.00</b></td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger btn-remove">&times;</button>
        </td>
    `;

    tbody.appendChild(tr);

    // load products into dropdown
    loadProducts(tr);

    // events
    tr.querySelector('.btn-remove').addEventListener('click', () => {
        tr.remove();
        calcTotals();
    });

    ['input','change'].forEach(ev => {
        tr.querySelector('.qty').addEventListener(ev, calcTotals);
        tr.querySelector('.unit_price').addEventListener(ev, calcTotals);
        tr.querySelector('.tax_rate').addEventListener(ev, calcTotals);
        tr.querySelector('.item_name').addEventListener(ev, calcTotals);
    });

    tr.querySelector('.product_id').addEventListener('change', async (e) => {
        const pid = e.target.value;
        if(!pid) return; // custom item
        const res = await fetch(`${PRODUCT_DETAILS_URL}/${pid}/details`);
        const json = await res.json();
        if(json.status){
            const d = json.data;
            tr.querySelector('.item_name').value = d.name || '';
            tr.querySelector('.unit_price').value = d.sale_price || 0;
            tr.querySelector('.tax_rate').value = document.getElementById('tax_enabled')?.checked ? GLOBAL_TAX_RATE : 0;
            calcTotals();
        }
    });

    calcTotals();
}

async function loadProducts(tr){
    const sel = tr.querySelector('.product_id');

    const res = await fetch(PRODUCT_OPTIONS_URL);
    const rows = await res.json();

    let html = `<option value="">-- Custom Item --</option>`;
    rows.forEach(r => {
        html += `<option value="${r.id}">${escapeHtml(r.name)}</option>`;
    });
    sel.innerHTML = html;
}

function calcTotals(){
    let sub = 0;
    let tax = 0;

    document.querySelectorAll('#itemsTable tbody tr').forEach(tr => {
        const qty = parseFloat(tr.querySelector('.qty').value || 0);
        const price = parseFloat(tr.querySelector('.unit_price').value || 0);
        const rate = document.getElementById('tax_enabled')?.checked ? GLOBAL_TAX_RATE : 0;
        tr.querySelector('.tax_rate').value = rate;

        const base = qty * price;
        const taxAmount = rate > 0 ? (base * rate / 100) : 0;
        const line = base + taxAmount;

        tr.querySelector('.line_total_text').innerText = line.toFixed(2);

        sub += base;
        tax += taxAmount;
    });

    const discount = parseFloat(document.getElementById('discount_amount').value || 0);
    const mode = document.querySelector('[name=calculate_tax]')?.value || 'after_discount';
    const taxBase = mode === 'before_discount' ? sub : Math.max(0, sub - discount);
    tax = document.getElementById('tax_enabled')?.checked ? (taxBase * GLOBAL_TAX_RATE / 100) : 0;
    const grand = Math.max(0, sub - discount) + tax;

    document.getElementById('subTotalText').innerText = sub.toFixed(2);
    document.getElementById('taxText').innerText = tax.toFixed(2);
    document.getElementById('grandTotalText').innerText = grand.toFixed(2);
}

function escapeHtml(text){
  return (text ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
}
</script>
@endpush
