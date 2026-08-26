@extends('backend.master')
@section('title','Follow-ups')
@section('maincontent')
<div class="nxl-content">
    <div class="page-header"><div class="page-header-left"><div class="page-header-title"><h5 class="m-b-10">My Follow-ups</h5></div></div></div>
</div>
<div class="main-content">
    @if(session('message'))<div class="alert alert-success">{{ session('message') }}</div>@endif
    <div class="row g-3 mb-3">
        <div class="col-md-4"><a class="text-decoration-none" href="{{ route('followups.index',['filter'=>'overdue']) }}"><div class="card p-3 border-danger"><small class="text-muted">Overdue</small><h3 class="text-danger mb-0">{{ $counts['overdue'] }}</h3></div></a></div>
        <div class="col-md-4"><a class="text-decoration-none" href="{{ route('followups.index',['filter'=>'today']) }}"><div class="card p-3 border-primary"><small class="text-muted">Today</small><h3 class="text-primary mb-0">{{ $counts['today'] }}</h3></div></a></div>
        <div class="col-md-4"><a class="text-decoration-none" href="{{ route('followups.index',['filter'=>'upcoming']) }}"><div class="card p-3 border-success"><small class="text-muted">Next 7 Days</small><h3 class="text-success mb-0">{{ $counts['upcoming'] }}</h3></div></a></div>
    </div>

    <div class="card">
        <div class="card-body">
            <form class="row g-2 mb-3" method="GET">
                <div class="col-md-3"><select name="filter" class="form-control"><option value="today" @selected($filter==='today')>Today</option><option value="overdue" @selected($filter==='overdue')>Overdue</option><option value="upcoming" @selected($filter==='upcoming')>Next 7 Days</option><option value="all" @selected($filter==='all')>All</option></select></div>
                <div class="col-md-6"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Search name, phone or lead no"></div>
                <div class="col-md-3"><button class="btn btn-primary w-100">Search</button></div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Lead Person</th><th>Phone</th><th>Action</th><th>Follow-up Time</th><th>Assigned To</th><th width="160">Work</th></tr></thead>
                    <tbody>
                    @forelse($followups as $lead)
                        @php $overdue = $lead->next_followup_at && $lead->next_followup_at->isPast() && !$lead->next_followup_at->isToday(); @endphp
                        <tr>
                            <td><strong>{{ $lead->person_name ?: ($lead->organization->name ?? '-') }}</strong><br><small class="text-muted">{{ $lead->lead_no }}</small></td>
                            <td><a href="tel:{{ $lead->person_phone }}">{{ $lead->person_phone ?: '-' }}</a></td>
                            <td><span class="badge bg-light text-dark">{{ ucfirst($lead->next_action_type ?: 'follow-up') }}</span></td>
                            <td><span class="{{ $overdue ? 'text-danger fw-bold':'' }}">{{ optional($lead->next_followup_at)->format('d M Y, h:i A') }}</span>@if($overdue)<br><small class="text-danger">Overdue</small>@endif</td>
                            <td>{{ $lead->assignedUser->name ?? '-' }}</td>
                            <td><button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#doneModal{{ $lead->id }}">✓ Follow-up Done</button></td>
                        </tr>
                        <div class="modal fade" id="doneModal{{ $lead->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="POST" action="{{ route('followups.complete',$lead->id) }}">@csrf
                            <div class="modal-header"><h5 class="modal-title">Complete Follow-up - {{ $lead->person_name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body">
                                <label class="form-label">What happened?</label><select name="outcome_status" class="form-control mb-3" required><option value="Interested">Interested</option><option value="Call Again">Call Again</option><option value="Meeting Fixed">Meeting Fixed</option><option value="No Answer">No Answer</option><option value="Not Interested">Not Interested</option><option value="Other">Other</option></select>
                                <label class="form-label">Note</label><textarea name="activity_text" class="form-control mb-3" rows="3"></textarea>
                                <label class="form-label">Next Follow-up (leave blank if finished)</label><input type="datetime-local" name="next_followup_at" class="form-control mb-3">
                                <label class="form-label">Next Action</label><select name="next_action_type" class="form-control"><option value="">No next action</option><option value="call">Call</option><option value="visit">Visit</option><option value="message">Message</option><option value="meeting">Meeting</option></select>
                            </div><div class="modal-footer"><button class="btn btn-success">Save Follow-up</button></div>
                        </form></div></div></div>
                    @empty<tr><td colspan="6" class="text-center py-5 text-muted">No follow-ups found.</td></tr>@endforelse
                    </tbody>
                </table>
            </div>
            {{ $followups->links() }}
        </div>
    </div>
</div>
@endsection
