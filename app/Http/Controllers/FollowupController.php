<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
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
        $this->middleware('permission:followup.details.view')->only(['history']);
        $this->middleware('permission:lead.activity.add')->only(['complete','feedback','reschedule']);
    }

    private function scopeVisible($query)
    {
        $u = Auth::user();

        if ($u->can('lead.view_all_branches')) {
            return $query;
        }

        return $query->where('assigned_user_id', $u->id);
    }

    private function ensureVisible(Lead $lead): void
    {
        $u = Auth::user();
        if ($u->can('lead.view_all_branches')) return;
        if ((int)$lead->assigned_user_id === (int)$u->id) return;
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

        $u = Auth::user();
        if ($u->can('lead.view_all_branches') && $u->can('staff.filter') && $request->filled('user_id')) $query->where('assigned_user_id', $request->integer('user_id'));

        if ($request->filled('q')) {
            $s = trim($request->q);
            $query->where(function ($q) use ($s) {
                $q->where('person_name', 'like', "%{$s}%")
                    ->orWhere('person_phone', 'like', "%{$s}%")
                    ->orWhere('lead_no', 'like', "%{$s}%");
            });
        }

        $followups = $query->orderBy('next_followup_at')->paginate(25)->withQueryString();
        $staffs = $u->can('lead.view_all_branches') && $u->can('staff.filter')
            ? User::where('status',1)->orderBy('name')->get(['id','name'])
            : User::whereKey($u->id)->get(['id','name']);

        $base = Lead::query()->where('lead_state', 'open')->whereNotNull('next_followup_at');
        $this->scopeVisible($base);
        $counts = [
            'overdue' => (clone $base)->where('next_followup_at', '<', now()->startOfDay())->count(),
            'today' => (clone $base)->whereBetween('next_followup_at', [now()->startOfDay(), now()->endOfDay()])->count(),
            'upcoming' => (clone $base)->whereBetween('next_followup_at', [now()->endOfDay(), now()->addDays(7)->endOfDay()])->count(),
        ];

        return view('backend.content.followups.index', compact('followups','filter','counts','staffs'));
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
            'activity_type' => ($data['next_action_type'] ?? null) ?: 'note',
            'activity_text' => ($data['activity_text'] ?? null) ?: ('Follow-up completed: '.$data['outcome_status']),
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

    public function feedback(Request $request, $id)
    {
        $lead=Lead::findOrFail($id); $this->ensureVisible($lead);
        $data=$request->validate(['feedback'=>'required|string|max:2000']);
        LeadActivity::create(['lead_id'=>$lead->id,'activity_type'=>'feedback','activity_text'=>$data['feedback'],'activity_at'=>now(),'outcome_status'=>'Feedback','created_by'=>Auth::id()]);
        $lead->update(['last_activity_at'=>now()]);
        return back()->with('message','Feedback saved successfully.');
    }

    public function reschedule(Request $request, $id)
    {
        $lead=Lead::findOrFail($id); $this->ensureVisible($lead);
        $data=$request->validate(['followup_date'=>'required|date','followup_time'=>'required|date_format:H:i','message'=>'nullable|string|max:2000','remarks'=>'nullable|string|max:1000','next_action_type'=>'nullable|in:call,visit,message,meeting']);
        $newAt=Carbon::parse($data['followup_date'].' '.$data['followup_time']);
        $oldAt=$lead->next_followup_at;
        $completed=LeadActivity::create(['lead_id'=>$lead->id,'activity_type'=>'follow-up','activity_text'=>'Previous follow-up completed by reschedule'.($data['remarks']?' — '.$data['remarks']:''),'activity_at'=>now(),'outcome_status'=>'Completed / Rescheduled','next_followup_at'=>$newAt,'next_action_type'=>$data['next_action_type']??$lead->next_action_type,'created_by'=>Auth::id()]);
        $new=LeadActivity::create(['lead_id'=>$lead->id,'activity_type'=>'rescheduled','activity_text'=>($data['message']??null) ?: 'Follow-up rescheduled','activity_at'=>$newAt,'outcome_status'=>'Scheduled','next_followup_at'=>$newAt,'next_action_type'=>$data['next_action_type']??$lead->next_action_type,'created_by'=>Auth::id(),'rescheduled_from_id'=>$completed->id]);
        $lead->update(['next_followup_at'=>$newAt,'next_action_type'=>$data['next_action_type']??$lead->next_action_type,'last_activity_at'=>now()]);
        return back()->with('message','Follow-up completed and rescheduled successfully.');
    }

    public function history($id)
    {
        $lead=Lead::with(['organization','assignedUser'])->findOrFail($id); $this->ensureVisible($lead);
        $items=LeadActivity::with('creator')->where('lead_id',$lead->id)->latest('activity_at')->paginate(30);
        return view('backend.content.followups.history',compact('lead','items'));
    }

}
