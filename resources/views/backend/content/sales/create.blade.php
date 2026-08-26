@extends('backend.master')

@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }} - Create Sale
@endsection

@section('maincontent')
<div class="nxl-content">

    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Create Sale</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item">CRM</li>
                <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">Sales</a></li>
                <li class="breadcrumb-item">Create</li>
            </ul>
        </div>

        <div class="page-header-right ms-auto">
            <a href="{{ route('sales.index') }}" class="btn btn-light-brand">Back</a>
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

                <form method="POST" action="{{ route('sales.store') }}" id="saleForm">
                    @csrf

                    <input type="hidden" name="lead_id" value="{{ $lead?->id }}">
                    <input type="hidden" name="quotation_id" value="{{ $quotation?->id }}">

                    <input type="hidden" name="organization_id" value="{{ $lead?->organization_id ?? $quotation?->organization_id }}">
                    <input type="hidden" name="organization_contact_id" value="{{ $lead?->organization_contact_id ?? $quotation?->organization_contact_id }}">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Client Name</label>
                            <input class="form-control" name="client_name" value="{{ $lead?->person_name ?? $quotation?->client_name }}" placeholder="Client name">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Client Phone</label>
                            <input class="form-control" name="client_phone" value="{{ $lead?->person_phone ?? $quotation?->client_phone }}" placeholder="Phone">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Client Email</label>
                            <input class="form-control" name="client_email" value="{{ $lead?->person_email ?? $quotation?->client_email }}" placeholder="Email">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Sale Date</label>
                            <input type="date" class="form-control" name="sale_date" value="{{ now()->toDateString() }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Sales Status</label>
                            <select class="form-control" name="status_stage_id">
                                <option value="">--</option>
                                @foreach($statuses as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Discount</label>
                            <input type="number" step="0.01" class="form-control" name="discount_amount" id="discount_amount"
                                   value="{{ $quotation?->discount_amount ?? 0 }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label d-block">VAT / Tax</label>
                            <div class="form-check mt-2">
                                <input type="hidden" name="tax_enabled" value="0">
                                <input class="form-check-input" type="checkbox" name="tax_enabled" id="tax_enabled" value="1" {{ $quotation?->tax_enabled ? 'checked' : '' }}>
                                <label class="form-check-label" for="tax_enabled">Include VAT / Tax ({{ number_format($taxRate,2) }}%)</label>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </div>

                    <hr class="my-4">

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
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Sub Total</span>
                                    <b id="subTotalText">0.00</b>
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
                        <a href="{{ route('sales.index') }}" class="btn btn-light-brand">Cancel</a>
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
const PREFILL_ITEMS = @json($prefillItems ?? []);

document.addEventListener('DOMContentLoaded', () => {

    if(PREFILL_ITEMS && PREFILL_ITEMS.length){
        PREFILL_ITEMS.forEach(it => addRow(it));
    }else{
        addRow();
    }

    document.getElementById('btnAddRow').addEventListener('click', () => addRow());
    document.getElementById('discount_amount').addEventListener('input', calcTotals);
document.getElementById('tax_enabled')?.addEventListener('change', calcTotals);

    calcTotals();
});

function addRow(item = null){
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

    loadProducts(tr, item?.product_id ?? null).then(() => {
        if(item){
            tr.querySelector('.item_name').value = item.item_name ?? '';
            tr.querySelector('.item_desc').value = item.description ?? '';
            tr.querySelector('.qty').value = item.qty ?? 1;
            tr.querySelector('.unit').value = item.unit ?? 'pcs';
            tr.querySelector('.unit_price').value = item.unit_price ?? 0;
            tr.querySelector('.tax_rate').value = item.tax_rate ?? 0;
        }
        calcTotals();
    });

    tr.querySelector('.btn-remove').addEventListener('click', () => {
        tr.remove(); calcTotals();
    });

    ['input','change'].forEach(ev => {
        tr.querySelector('.qty').addEventListener(ev, calcTotals);
        tr.querySelector('.unit_price').addEventListener(ev, calcTotals);
        tr.querySelector('.tax_rate').addEventListener(ev, calcTotals);
        tr.querySelector('.item_name').addEventListener(ev, calcTotals);
    });

    tr.querySelector('.product_id').addEventListener('change', async (e) => {
        const pid = e.target.value;
        if(!pid) return;
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

async function loadProducts(tr, selectedId=null){
    const sel = tr.querySelector('.product_id');
    const res = await fetch(PRODUCT_OPTIONS_URL);
    const rows = await res.json();
    let html = `<option value="">-- Custom Item --</option>`;
    rows.forEach(r => html += `<option value="${r.id}">${escapeHtml(r.name)}</option>`);
    sel.innerHTML = html;
    if(selectedId) sel.value = selectedId;
}

function calcTotals(){
    let sub = 0; let tax = 0;

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
    const grand = (sub - discount) + tax;

    document.getElementById('subTotalText').innerText = sub.toFixed(2);
    document.getElementById('taxText').innerText = tax.toFixed(2);
    document.getElementById('grandTotalText').innerText = grand.toFixed(2);
}

function escapeHtml(text){
  return (text ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
}
</script>
@endpush