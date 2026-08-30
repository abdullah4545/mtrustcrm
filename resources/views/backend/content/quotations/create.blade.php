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

                        <div class="col-md-8">
                            <label class="form-label">Client Address</label>
                            <input class="form-control" name="client_address" value="{{ $lead?->organization?->address }}" placeholder="Client / organization address">
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

                        <div class="col-md-8">
                            <label class="form-label">Quotation Subject</label>
                            <input class="form-control" name="subject" value="{{ $lead?->subject }}" placeholder="e.g. Supply of Medical Equipment">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Salutation</label>
                            <input class="form-control" name="salutation" value="Dear Sir," placeholder="Dear Sir,">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Cover Letter</label>
                            <textarea class="form-control quotation-editor" id="cover_letter" name="cover_letter" rows="6"><p>Thank you for taking an interest in our products and services. We are pleased to submit our quotation for your kind consideration. The detailed financial quotation, product description, product image, prices and applicable terms &amp; conditions are enclosed on the following pages.</p><p>We will consider ourselves fortunate if we are successful in establishing a valued business relationship with your organization.</p></textarea>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Quotation Description / Additional Details</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Optional additional quotation details..."></textarea>
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
                            <label class="form-label">Note for Recipient</label>
                            <textarea class="form-control quotation-editor" id="note_for_recipient" name="note_for_recipient" rows="3" placeholder="Optional note for recipient"></textarea>

                            <label class="form-label mt-3">Terms Section Title</label>
                            <input class="form-control" name="terms_title" value="TERMS AND CONDITIONS">

                            <label class="form-label mt-3">Terms & Conditions</label>
                            <textarea class="form-control quotation-editor" id="terms" name="terms" rows="7"><p><b>Payment:</b> As mutually agreed.</p><p><b>Delivery:</b> As per confirmed order and stock availability.</p><p><b>Installation:</b> As applicable for the quoted product.</p><p><b>After Sales:</b> Service support will be provided according to the applicable warranty terms.</p></textarea>

                            <label class="form-label mt-3">Closing / Final Note</label>
                            <textarea class="form-control quotation-editor" id="closing_note" name="closing_note" rows="3"><p>We thank you and assure you of our best attention at all times.</p></textarea>

                            <label class="form-label mt-3">Regards / Sign-off</label>
                            <input class="form-control" name="sign_off" value="Best Regards" placeholder="Best Regards">
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
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
const quotationEditors = {};
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.quotation-editor').forEach(el => {
        ClassicEditor.create(el, { toolbar: ['heading','|','bold','italic','link','bulletedList','numberedList','|','undo','redo'] })
            .then(editor => { quotationEditors[el.id] = editor; })
            .catch(console.error);
    });
    document.getElementById('quotationForm')?.addEventListener('submit', () => {
        Object.entries(quotationEditors).forEach(([id, editor]) => {
            const source = document.getElementById(id);
            if(source) source.value = editor.getData();
        });
    });
});
</script>
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
            tr.querySelector('.item_desc').value = d.description || d.configuration_description || '';
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
