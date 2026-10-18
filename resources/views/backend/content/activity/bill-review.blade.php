@extends('backend.master')
@section('title','Activity Bill Review')
@section('maincontent')
<div class="nxl-content"><div class="page-header"><h5>Activity #{{ $activity->id }} — TA / DA Review</h5><a href="{{ route('activities.index') }}" class="btn btn-light">Back to Activities</a></div>
<div class="main-content">
@if(session('message'))<div class="alert alert-success">{{ session('message') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<div class="card"><div class="card-body">
<p><strong>{{ $activity->creator?->name }}</strong> · {{ ($activity->activity_at ?? $activity->date)?->timezone('Asia/Dhaka')->format('d M Y, h:i A') }} · {{ ucfirst($activity->status) }} · {{ ucwords(str_replace('_',' ',$activity->payment_status)) }}</p>
@if($activity->review_note)<div class="alert {{ $activity->status==='rejected'?'alert-danger':'alert-info' }}"><strong>Activity review reason:</strong> {{ $activity->review_note }}</div>@endif
<p>{{ $activity->organization_name ?: 'No organization' }} @if($activity->contact_person) · {{ $activity->contact_person }} @endif</p><p>{{ $activity->work_details }}</p>
<div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Bill</th><th>Submitted</th><th>Deducted</th><th>Payable</th></tr></thead><tbody>
<tr><td>TA</td><td>{{ number_format($activity->claimed_ta,2) }}</td><td>{{ number_format($activity->claimed_ta-$activity->ta,2) }}</td><td>{{ number_format($activity->ta,2) }}</td></tr>
<tr><td>DA</td><td>{{ number_format($activity->claimed_da,2) }}</td><td>{{ number_format($activity->claimed_da-$activity->da,2) }}</td><td>{{ number_format($activity->da,2) }}</td></tr>
<tr class="fw-bold"><td>Total</td><td>{{ number_format($activity->claimed_total,2) }}</td><td>{{ number_format($activity->deducted_total,2) }}</td><td>{{ number_format($activity->total,2) }}</td></tr>
</tbody></table></div>
@if($activity->payment_status==='paid')<p class="text-muted">Paid bills are locked. Review history remains available below.</p>@endif
@if($activity->status==='rejected')<p class="text-danger">The entire activity is rejected; payable is zero. Entry claims and deduction history remain preserved.</p>@endif
</div></div>
@foreach(['ta'=>$activity->travels,'da'=>$activity->expenses] as $type=>$entries)
<div class="card"><div class="card-header"><h6 class="mb-0">{{ strtoupper($type) }} Entries</h6></div><div class="card-body">
@forelse($entries as $entry)
@php
$claimed=$type==='ta'?$entry->cost:$entry->amount;
$payable=$activity->status==='rejected'?0:max(0,(float)$claimed-(float)$entry->deduction_amount);
@endphp
<div class="border rounded p-3 mb-3">
<div class="d-flex flex-wrap justify-content-between gap-2"><strong>{{ strtoupper($type) }} #{{ $entry->id }}: {{ $type==='ta'? $entry->from_location.' → '.$entry->to_location : $entry->expense_type }}</strong><span class="badge bg-secondary">{{ ucfirst($entry->bill_status) }}</span></div>
@if($type==='ta' && $entry->tour_type==='tour')<span class="badge bg-primary mt-2">Tour</span>@endif
<p class="small text-muted mt-2 mb-1">{{ $entry->entry_at?->timezone('Asia/Dhaka')->format('d M Y, h:i A') }}</p>
<p class="mt-2 mb-1">Submitted: <strong>{{ number_format($claimed,2) }}</strong> · Entry deduction: <strong>{{ number_format($entry->deduction_amount,2) }}</strong> · Payable: <strong>{{ number_format($payable,2) }}</strong> TK</p>
@if($entry->deduction_reason)<p class="mb-1"><strong>Reason:</strong> {{ $entry->deduction_reason }}</p>@endif
@if($entry->bill_reviewed_at)<p class="small text-muted">Reviewed by {{ $entry->billReviewer?->name ?? 'Former user' }} · {{ $entry->bill_reviewed_at->timezone('Asia/Dhaka')->format('d M Y, h:i A') }}</p>@endif
@if($entry->image_url)<a href="{{ \Illuminate\Support\Str::startsWith($entry->image_url,['https://','http://']) ? $entry->image_url : asset($entry->image_url) }}" target="_blank" rel="noopener">View submitted bill</a>@endif
@if(auth()->user()->can('activity.approve') && $activity->payment_status!=='paid' && $activity->status!=='rejected')
<details class="mt-2"><summary class="btn btn-sm btn-outline-primary">Review / Adjust this entry</summary>
<form method="POST" action="{{ route('activities.bill-review.update',$activity->id) }}" class="mt-3 bill-entry-form">@csrf
<input type="hidden" name="entry_type" value="{{ $type }}"><input type="hidden" name="entry_id" value="{{ $entry->id }}"><input type="hidden" name="version" value="{{ \App\Services\ActivityBillReview::version($entry) }}">
<div class="row g-2"><div class="col-md-4"><label class="form-label">Decision</label><select name="decision" class="form-select bill-decision"><option value="deduct">Deduct an amount</option><option value="approve">Approve / remove deduction</option><option value="reject">Reject whole entry</option></select></div>
<div class="col-md-4"><label class="form-label">Deduction (TK)</label><input name="deduction_amount" type="number" min="0.01" max="{{ $claimed }}" step="0.01" class="form-control bill-deduction" value="{{ $entry->deduction_amount>0?$entry->deduction_amount:'' }}" required></div>
<div class="col-12"><label class="form-label">Reason *</label><textarea name="reason" required maxlength="2000" rows="2" class="form-control" placeholder="Explain this entry's deduction, rejection or correction"></textarea></div>
<div class="col-12"><button class="btn btn-primary">Save review</button></div></div>
</form></details>
@endif
</div>
@empty<p class="text-muted">No individual {{ strtoupper($type) }} entries. Legacy aggregate bills remain visible in the summary.</p>@endforelse
</div></div>
@endforeach
<div class="card"><div class="card-header"><h6>Review History</h6></div><div class="card-body table-responsive"><table class="table table-bordered"><thead><tr><th>When / Who</th><th>Entry</th><th>Claim</th><th>Deduction</th><th>Status</th><th>Reason</th></tr></thead><tbody>
@forelse($history as $log)<tr><td>{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y, h:i A') }}<br>{{ $log->reviewer_name }}</td><td>{{ strtoupper($log->entry_type) }} #{{ $log->entry_id }}<br>{{ $log->entry_label }}</td><td>{{ number_format($log->claimed_amount,2) }}</td><td>{{ number_format($log->old_deduction,2) }} → {{ number_format($log->new_deduction,2) }}</td><td>{{ ucfirst($log->old_status) }} → {{ ucfirst($log->new_status) }}</td><td>{{ $log->reason }}</td></tr>@empty<tr><td colspan="6">No reviews yet.</td></tr>@endforelse
</tbody></table></div></div>
</div></div>
@endsection
@push('scripts')
<script>
document.querySelectorAll('.bill-decision').forEach(select=>select.addEventListener('change',function(){const input=this.closest('form').querySelector('.bill-deduction');input.disabled=this.value!=='deduct';input.required=this.value==='deduct';}));
</script>
@endpush
