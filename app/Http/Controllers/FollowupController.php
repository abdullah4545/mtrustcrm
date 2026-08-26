<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\CrmAccess;

class FollowupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:lead.view_all_branches|lead.view_branch|lead.view_self')->only(['index']);
        $this->middleware('permission:lead.activity.add')->only(['complete']);
    }

    private function scopeVisible($query)
    {
        $u = Auth::user();

        if ($u->can('lead.view_all_branches')) {
            return $query;
        }

        if ($u->can('lead.view_branch')) {
            return $query->where('branch_id', $u->branch_id);
        }

        return $query->where('assigned_user_id', $u->id);
    }

    private function ensureVisible(Lead $lead): void
    {
        $u = Auth::user();
        if (CrmAccess::isStaff($u)) { abort_unless((int)$lead->assigned_user_id === (int)$u->id,403); return; }
        if ($u->can('lead.view_all_branches')) return;
        if ($u->can('lead.view_branch') && (int)$lead->branch_id === (int)$u->branch_id) return;
        if ($u->can('lead.view_self') && (int)$lead->assigned_user_id === (int)$u->id) return;
        abort(403);
    }

    public function index(Request $request)
    {
        $query = Lead::with(['statusStage:id,name,color','organization:id,name','assignedUser:id,name'])
            ->where('lead_state', 'open')
            ->whereNotNull('next_followup_at');

        $this->scopeVisible($query);

        $filter = $request->get('filter', 'today');
        if ($filter === 'overdue') {
            $query->where('next_followup_at', '<', now()->startOfDay());
        } elseif ($filter === 'upcoming') {
            $query->whereBetween('next_followup_at', [now()->endOfDay(), now()->addDays(7)->endOfDay()]);
        } elseif ($filter === 'all') {
            // no date condition
        } else {
            $query->whereBetween('next_followup_at', [now()->startOfDay(), now()->endOfDay()]);
        }

        if ($request->filled('q')) {
            $s = trim($request->q);
            $query->where(function ($q) use ($s) {
                $q->where('person_name', 'like', "%{$s}%")
                    ->orWhere('person_phone', 'like', "%{$s}%")
                    ->orWhere('lead_no', 'like', "%{$s}%");
            });
        }

        $followups = $query->orderBy('next_followup_at')->paginate(25)->withQueryString();

        $base = Lead::query()->where('lead_state', 'open')->whereNotNull('next_followup_at');
        $this->scopeVisible($base);
        $counts = [
            'overdue' => (clone $base)->where('next_followup_at', '<', now()->startOfDay())->count(),
            'today' => (clone $base)->whereBetween('next_followup_at', [now()->startOfDay(), now()->endOfDay()])->count(),
            'upcoming' => (clone $base)->whereBetween('next_followup_at', [now()->endOfDay(), now()->addDays(7)->endOfDay()])->count(),
        ];

        return view('backend.content.followups.index', compact('followups','filter','counts'));
    }

    public function complete(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);
        $this->ensureVisible($lead);

        $data = $request->validate([
            'outcome_status' => 'required|string|max:50',
            'activity_text' => 'nullable|string|max:1000',
            'next_followup_at' => 'nullable|date',
            'next_action_type' => 'nullable|in:call,visit,message,meeting',
        ]);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'activity_type' => $data['next_action_type'] ?: 'note',
            'activity_text' => $data['activity_text'] ?: ('Follow-up completed: '.$data['outcome_status']),
            'activity_at' => now(),
            'outcome_status' => $data['outcome_status'],
            'next_followup_at' => $data['next_followup_at'] ?? null,
            'next_action_type' => $data['next_action_type'] ?? null,
            'created_by' => Auth::id(),
        ]);

        $lead->update([
            'next_followup_at' => $data['next_followup_at'] ?? null,
            'next_action_type' => $data['next_action_type'] ?? null,
            'last_activity_at' => now(),
        ]);

        return back()->with('message', 'Follow-up completed successfully.');
    }
}
