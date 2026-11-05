<div class="row g-3">
@include('backend.partials.sale-organization', ['organizationColumn' => 'col-md-6'])
<div class="col-md-6"><label class="form-label">Sale Date</label><input type="date" name="sale_date" class="form-control" value="{{ old('sale_date', isset($sale) ? optional($sale->sale_date)->format('Y-m-d') : now()->toDateString()) }}"></div>
<div class="col-md-4"><label class="form-label">Sales Status</label><select name="status_stage_id" class="form-control"><option value="">--</option>@foreach($statuses as $status)<option value="{{ $status->id }}" @selected(old('status_stage_id', $sale->status_stage_id ?? null) == $status->id)>{{ $status->name }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Total Amount *</label><input type="number" min="0" max="9999999999.99" step="0.01" name="grand_total" id="direct_total" class="form-control" required value="{{ old('grand_total', $sale->grand_total ?? $quotation->grand_total ?? $lead->expected_value ?? 0) }}"></div>
<div class="col-md-4"><label class="form-label">Due Amount *</label><input type="number" min="0" step="0.01" name="due_total" id="direct_due" class="form-control" required value="{{ old('due_total', $sale->due_total ?? $quotation->grand_total ?? $lead->expected_value ?? 0) }}"></div>
<div class="col-12"><div class="alert alert-light border">Paid Amount: <strong id="direct_paid">0.00</strong><br><small>Paid = Total − Due. Existing recorded payments are preserved when editing.</small></div></div>
<div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="3">{{ old('notes', $sale->notes ?? '') }}</textarea></div>
</div>
@push('scripts')
<script>
$(function(){function totals(){const total=Number($('#direct_total').val())||0;const due=Number($('#direct_due').val())||0;$('#direct_due').attr('max',total);$('#direct_paid').text((total-due).toFixed(2));}$('#direct_total,#direct_due').on('input',totals);totals();});
</script>
@endpush
