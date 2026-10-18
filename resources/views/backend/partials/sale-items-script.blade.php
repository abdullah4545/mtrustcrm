<script>
const PRODUCT_OPTIONS_URL = "{{ route('products.options') }}";
const GLOBAL_TAX_RATE = Number(@json($taxRate ?? 0));
const PRODUCT_DETAILS_URL = "{{ url('products') }}"; // /{id}/details

let rowIndex = 0;
const INITIAL_ITEMS = @json(old('items', isset($sale) ? $sale->items : ($prefillItems ?? [])));

document.addEventListener('DOMContentLoaded', () => {

    if(INITIAL_ITEMS && INITIAL_ITEMS.length){
        INITIAL_ITEMS.forEach(it => addRow(it));
    }else{
        addRow();
    }

    document.getElementById('saleForm').addEventListener('submit',function(e){
        if(document.querySelector('#itemsTable tr[data-loading="1"]')){e.preventDefault();alert('Please wait for the product price to load.');}
    });
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
            <input class="form-control mt-2 item_name" name="items[${idx}][item_name]" placeholder="Item name" aria-label="Item name (required)" required>
        </td>
        <td>
            <textarea class="form-control item_desc" name="items[${idx}][description]" rows="2" placeholder="Description (optional)"></textarea>
        </td>
        <td><input type="number" step="0.01" class="form-control qty" min="0.01" required name="items[${idx}][qty]" value="1"></td>
        <td><input type="text" class="form-control unit" name="items[${idx}][unit]" value="pcs"></td>
        <td><input type="number" step="0.01" class="form-control unit_price" min="0" required name="items[${idx}][unit_price]" value="0"></td>
        <td><input type="number" step="0.01" class="form-control tax_rate" name="items[${idx}][tax_rate]" value="0"></td>
        <td><b class="line_total_text">0.00</b></td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger btn-remove">&times;</button>
        </td>
    `;
    tr.dataset.loading='1';
    tr._savedItemName=item?.item_name || '';
    tbody.appendChild(tr);
    tr.querySelector('.product_id').disabled=true;

    loadProducts(tr, item?.product_id ?? null).then(() => {
        if(item){
            tr.querySelector('.item_name').value = item.item_name ?? '';
            tr.querySelector('.item_desc').value = item.description ?? '';
            tr.querySelector('.qty').value = item.qty ?? 1;
            tr.querySelector('.unit').value = item.unit ?? 'pcs';
            tr.querySelector('.unit_price').value = item.unit_price ?? 0;
            tr.querySelector('.tax_rate').value = item.tax_rate ?? 0;
        }
        delete tr.dataset.loading;
        tr.querySelector('.product_id').disabled=false;
        syncItemName(tr);
        calcTotals();
    }).catch(() => showRowError(tr, 'Could not load products. Please reload and try again.'));

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
        tr.querySelector('.item-error')?.remove();
        const pid = e.target.value;
        const version = tr._productVersion = (tr._productVersion || 0) + 1;
        tr.querySelector('.item_name').value = '';
        syncItemName(tr);
        if(!pid){ delete tr.dataset.loading; tr.querySelector('.unit_price').value=0; calcTotals(); return; }
        tr.dataset.loading='1';
        try {
            const res = await fetch(`${PRODUCT_DETAILS_URL}/${encodeURIComponent(pid)}/details`);
            if(!res.ok) throw new Error('Product lookup failed');
            const json = await res.json();
            if(!json.status || !json.data) throw new Error('Product unavailable');
            if(tr._productVersion !== version || !tr.isConnected) return;
            const d=json.data;
            tr.querySelector('.item_name').value=d.name || '';
            tr.querySelector('.unit_price').value=d.sale_price ?? 0;
            calcTotals();
        } catch(error) {
            if(tr._productVersion===version){
                e.target.value='';
                syncItemName(tr);
                showRowError(tr,'Could not load the selected product. Please select it again.');
            }
        } finally { if(tr._productVersion===version) delete tr.dataset.loading; }
    });

    calcTotals();
}

async function loadProducts(tr, selectedId=null){
    const sel = tr.querySelector('.product_id');
    const res = await fetch(PRODUCT_OPTIONS_URL);
    if(!res.ok) throw new Error('Product options unavailable');
    const rows = await res.json();
    if(!Array.isArray(rows)) throw new Error('Invalid product list');
    let html = `<option value="">-- Custom Item --</option>`;
    rows.forEach(r => html += `<option value="${r.id}">${escapeHtml(r.name)}</option>`);
    sel.innerHTML = html;
    if(selectedId){
        if(!Array.from(sel.options).some(o=>String(o.value)===String(selectedId))){
            const saved=document.createElement('option');saved.value=selectedId;saved.textContent=tr._savedItemName || 'Saved product';sel.appendChild(saved);
        }
        sel.value=selectedId;
    }
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

function syncItemName(tr){
    const custom=!tr.querySelector('.product_id').value;
    const name=tr.querySelector('.item_name');
    name.type=custom ? 'text' : 'hidden';
    name.required=custom;
    name.classList.toggle('mt-2',custom);
}
function showRowError(tr,message){
    let el=tr.querySelector('.item-error');
    if(!el){el=document.createElement('div');el.className='item-error text-danger small mt-1';tr.cells[0].appendChild(el);}
    el.textContent=message;
}
function escapeHtml(text){
  return String(text ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
}
</script>