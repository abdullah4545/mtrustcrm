<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Platform;
use App\Models\StatusStage;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\OrganizationContact;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use App\Support\CrmAccess;

class LeadController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
 
        $this->middleware('permission:lead.view_all_branches|lead.view_branch|lead.view_self')
            ->only([
                'index','datatable','show',
                'orgOptions','orgContacts','contactDetails',  
                'activities'  
            ]);
 
        $this->middleware('permission:lead.create')
            ->only(['store','quickCreate','quickStore']);
 
        $this->middleware('permission:lead.edit')
            ->only(['update']);
 
        $this->middleware('permission:lead.delete')
            ->only(['destroy']);
 
        $this->middleware('permission:lead.activity.view')
            ->only(['activities']);

        $this->middleware('permission:lead.activity.add')
            ->only(['storeActivity']);
    }
    public function index()
    {
        $platforms = Platform::where('status',1)->orderBy('title')->get(['id','title']);
        $statuses  = StatusStage::where('status',1)->where('is_for','lead')->orderBy('name')->get(['id','name','color']);

        $branches = [];
        if ($this->canSeeAllBranches()) {
            $branches = Branch::orderBy('branch_name')->get(['id','branch_name']);
        }

        $assignees = collect();
        if (Auth::user()->can('lead.view_all_branches')) {
            $assignees = User::where('status',1)->orderBy('name')->get(['id','name','branch_id']);
        } elseif (Auth::user()->can('lead.view_branch')) {
            $assignees = User::where('status',1)->where('branch_id',Auth::user()->branch_id)->orderBy('name')->get(['id','name','branch_id']);
        } else {
            $assignees = User::whereKey(Auth::id())->get(['id','name','branch_id']);
        }

        return view('backend.content.leads.index', compact('platforms','statuses','branches','assignees'));
    }

    public function quickCreate()
    {
        $platforms = Platform::where('status',1)->orderBy('title')->get(['id','title']);
        $statuses  = StatusStage::where('status',1)->where('is_for','lead')->orderBy('name')->get(['id','name','color']);
        $orgs      = CrmAccess::applyOrganizationScope(Organization::query())->where('status','active')->orderBy('name')->get();

        return view('backend.content.leads.quick_create', compact(
            'statuses',
            'platforms',
            'orgs'
        ));
    }

    // =========================
    // Quick Store Lead
    // =========================
    public function quickStore(Request $request)
    {
        $request->validate([
            'person_name'  => 'required',
            'person_phone' => 'required',
            'existing_machine' => 'nullable|string|max:255',
        ]);
        $u = Auth::user();
        if ($request->filled('organization_id')) {
            $org = Organization::findOrFail($request->organization_id);
            CrmAccess::ensureOrganizationAllowed($org, $u);
        }
 
        $lead = Lead::create([
            'lead_no' => $this->leadNo(),
            'branch_id' => $u->branch_id,
            'assigned_user_id' => ($u->can('lead.view_branch') || $u->can('lead.view_all_branches')) && $request->filled('assigned_user_id') ? (int)$request->assigned_user_id : $u->id,
            'created_by' => $u->id,
            'organization_id' => $request->organization_id,
            'organization_contact_id' => $request->organization_contact_id,

            'platform_id' => $request->platform_id,
            'status_stage_id' => $request->status_stage_id,

            'person_name' => $request->person_name,
            'person_phone' => $request->person_phone,
            'person_email' => $request->person_email,

            'subject' => $request->subject,
            'note' => $request->note,
            'expected_value' => (float)($request->expected_value ?? 0),
            'existing_machine' => $request->existing_machine,

            'next_followup_at' => $request->next_followup_at,
            'next_action_type' => $request->next_action_type,
            'last_activity_at' => now(),

            'lead_state' => 'open',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Lead created successfully',
            'data'    => $lead
        ]);
    }

    private function canSeeAllBranches(): bool
    {
        return Auth::user()->can('lead.view_all_branches');
    }

    private function applyVisibleScope($q)
    {
        $u = Auth::user();
        if (CrmAccess::isStaff($u)) return $q->where('assigned_user_id',$u->id);
        if ($u->can('lead.view_all_branches')) return $q;
        if ($u->can('lead.view_branch')) return $q->where('branch_id', $u->branch_id);
        return $q->where('assigned_user_id', $u->id);
    }

    private function ensureLeadAccess(Lead $lead): void
    {
        $u = Auth::user();
        if (CrmAccess::isStaff($u)) { abort_unless((int)$lead->assigned_user_id === (int)$u->id,403); return; }
        if ($u->can('lead.view_all_branches')) return;
        if ($u->can('lead.view_branch') && (int)$lead->branch_id === (int)$u->branch_id) return;
        if ($u->can('lead.view_self') && (int)$lead->assigned_user_id === (int)$u->id) return;
        abort(403);
    }

    private function leadNo(): string
    {
        return 'LD-' . date('ymd') . '-' . random_int(1000,9999);
    }

    private function applyContactAutofill(array $data, ?int $contactId): array
    {
        if(!$contactId) return $data;

        $c = OrganizationContact::find($contactId);
        if(!$c) return $data;

        // ✅ adjust if your contact fields differ
        $data['person_name']  = $c->name ?? $data['person_name'];
        $data['person_email'] = $c->email ?? $data['person_email'];

        // phone column name may differ in your table:
        $data['person_phone'] = $c->phone ?? ($c->phone_primary ?? $data['person_phone']);

        return $data;
    }

    public function datatable(Request $request)
    {
        $u = Auth::user();

        $q = Lead::query()
            ->with([
                'platform:id,title',
                'statusStage:id,name,color',
                'organization:id,name',
                'organizationContact:id,name'
            ])
            ->latest();

        // permission-aware visibility
        $this->applyVisibleScope($q);
        if ($this->canSeeAllBranches() && $request->filled('branch_id')) {
            $q->where('branch_id', $request->branch_id);
        }

        // filters
        if ($request->filled('platform_id')) $q->where('platform_id', $request->platform_id);
        if ($request->filled('status_stage_id')) $q->where('status_stage_id', $request->status_stage_id);
        if ($request->filled('organization_id')) $q->where('organization_id', $request->organization_id);

        if ($request->filled('lead_state')) $q->where('lead_state', $request->lead_state);

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $q->whereBetween('created_at', [$request->date_from.' 00:00:00', $request->date_to.' 23:59:59']);
        }

        if ($request->filled('search_text')) {
            $s = trim($request->search_text);
            $q->where(function($qq) use ($s){
                $qq->where('person_name','like',"%{$s}%")
                   ->orWhere('person_phone','like',"%{$s}%");
            });
        }

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('org_name', fn($row) => $row->organization ? e($row->organization->name) : '-')
            ->addColumn('contact_name', fn($row) => $row->organizationContact ? e($row->organizationContact->name) : '-')
            ->addColumn('platform_name', fn($row) => $row->platform ? e($row->platform->title) : '-')
            ->addColumn('status_badge', function($row){
                if(!$row->statusStage) return '-';
                return '<span style="display:inline-flex;align-items:center;gap:6px;">
                    <span style="width:10px;height:10px;border-radius:50%;background:'.$row->statusStage->color.';"></span>
                    <span class="badge bg-light text-dark" style="border:1px solid #eee;">'.e($row->statusStage->name).'</span>
                </span>';
            })
            ->addColumn('next_followup', function($row){
                if(!$row->next_followup_at) return '-';

                $dt = Carbon::parse($row->next_followup_at)->timezone('Asia/Dhaka');
                $show = $dt->format('d M Y, h:i A');

                $type = $row->next_action_type ? strtoupper($row->next_action_type) : null;

                $map = [
                    'CALL' => 'bg-primary',
                    'VISIT' => 'bg-success',
                    'MESSAGE' => 'bg-warning',
                    'MEETING' => 'bg-info',
                ];
                $cls = $map[$type] ?? 'bg-secondary';

                $badge = $type ? '<span class="badge '.$cls.' ms-2">'.$type.'</span>' : '';
                return $show.$badge;
            })
            ->editColumn('lead_state', function($row){
                return $row->lead_state === 'open'
                    ? '<span class="badge bg-success">Open</span>'
                    : '<span class="badge bg-secondary">Closed</span>';
            })
            ->addColumn('action', function($row){
                $html = '<div class="d-flex flex-wrap gap-1">';
                if (Auth::user()->can('lead.activity.add')) $html .= '<button class="btn btn-sm btn-info btn-activity" data-id="'.$row->id.'"><i class="feather-phone-call"></i> Follow-up</button>';
                if (Auth::user()->can('quotation.create')) $html .= '<a class="btn btn-sm btn-dark" href="'.route('leads.quotation.create',$row->id).'"><i class="feather-file-text"></i> Quotation</a>';
                if (Auth::user()->can('sale.create')) {
                    if (!empty($row->converted_sale_id)) $html .= '<span class="btn btn-sm btn-light disabled"><i class="feather-check"></i> Sale Created</span>';
                    else $html .= '<a class="btn btn-sm btn-success" href="'.route('leads.sales.create',$row->id).'"><i class="feather-shopping-cart"></i> Make Sale</a>';
                }
                if (Auth::user()->can('lead.edit')) $html .= '<button class="btn btn-sm btn-primary btn-edit" data-id="'.$row->id.'"><i class="feather-edit"></i> Edit</button>';
                if (Auth::user()->can('lead.delete')) $html .= '<button class="btn btn-sm btn-danger btn-delete" data-id="'.$row->id.'"><i class="feather-trash-2"></i></button>';
                return $html.'</div>';
            })
            ->rawColumns(['status_badge','lead_state','next_followup','action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'organization_id' => 'nullable|exists:organizations,id',
            'organization_contact_id' => 'nullable|exists:organization_contacts,id',

            'platform_id' => 'nullable|exists:platforms,id',
            'status_stage_id' => 'nullable|exists:status_stages,id',
            'assigned_user_id' => 'nullable|exists:users,id',

            'person_name' => 'required|string|max:150',
            'person_phone' => 'required|string|max:30',
            'person_email' => 'nullable|email|max:150',

            'subject' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'expected_value' => 'nullable|numeric|min:0',
            'existing_machine' => 'nullable|string|max:255',

            'next_followup_at' => 'nullable|date',
            'next_action_type' => 'nullable|string|max:30',
        ]);

        $u = Auth::user();
        if ($request->filled('organization_id')) {
            $org = Organization::findOrFail($request->organization_id);
            CrmAccess::ensureOrganizationAllowed($org, $u);
        }

        $data = [
            'lead_no' => $this->leadNo(),
            'branch_id' => $u->branch_id,
            'assigned_user_id' => ($u->can('lead.view_branch') || $u->can('lead.view_all_branches')) && $request->filled('assigned_user_id') ? (int)$request->assigned_user_id : $u->id,
            'created_by' => $u->id,

            'organization_id' => $request->organization_id,
            'organization_contact_id' => $request->organization_contact_id,

            'platform_id' => $request->platform_id,
            'status_stage_id' => $request->status_stage_id,

            'person_name' => $request->person_name,
            'person_phone' => $request->person_phone,
            'person_email' => $request->person_email,

            'subject' => $request->subject,
            'note' => $request->note,
            'expected_value' => (float)($request->expected_value ?? 0),
            'existing_machine' => $request->existing_machine,

            'next_followup_at' => $request->next_followup_at,
            'next_action_type' => $request->next_action_type,
            'last_activity_at' => now(),

            'lead_state' => 'open',
        ];

        // auto-fill from selected contact
        $data = $this->applyContactAutofill($data, $request->organization_contact_id);

        $lead = Lead::create($data);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'activity_type' => 'note',
            'activity_text' => 'Lead created',
            'activity_at' => now(),
            'next_followup_at' => $lead->next_followup_at,
            'next_action_type' => $lead->next_action_type,
            'created_by' => $u->id,
        ]);

        return response()->json(['status'=>true,'message'=>'Lead created successfully']);
    }

    public function show($id)
    {
        $u = Auth::user();
        $lead = Lead::findOrFail($id);

        $this->ensureLeadAccess($lead);

        return response()->json(['status'=>true,'data'=>$lead]);
    }

    public function update(Request $request, $id)
    {
        $u = Auth::user();
        $lead = Lead::findOrFail($id);
        $this->ensureLeadAccess($lead);

        $request->validate([
            'organization_id' => 'nullable|exists:organizations,id',
            'organization_contact_id' => 'nullable|exists:organization_contacts,id',

            'platform_id' => 'nullable|exists:platforms,id',
            'status_stage_id' => 'nullable|exists:status_stages,id',
            'assigned_user_id' => 'nullable|exists:users,id',

            'person_name' => 'nullable|string|max:150',
            'person_phone' => 'nullable|string|max:30',
            'person_email' => 'nullable|email|max:150',

            'subject' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'expected_value' => 'nullable|numeric|min:0',
            'existing_machine' => 'nullable|string|max:255',

            'next_followup_at' => 'nullable|date',
            'next_action_type' => 'nullable|string|max:30',

            'lead_state' => 'required|in:open,closed',
            'lost_reason' => 'nullable|string|max:255',
        ]);

        if ($request->filled('organization_id')) {
            $org = Organization::findOrFail($request->organization_id);
            CrmAccess::ensureOrganizationAllowed($org, $u);
        }

        $data = [
            'organization_id' => $request->organization_id,
            'organization_contact_id' => $request->organization_contact_id,

            'platform_id' => $request->platform_id,
            'status_stage_id' => $request->status_stage_id,
            'assigned_user_id' => ($u->can('lead.view_branch') || $u->can('lead.view_all_branches')) && $request->filled('assigned_user_id') ? (int)$request->assigned_user_id : $lead->assigned_user_id,

            'person_name' => $request->person_name,
            'person_phone' => $request->person_phone,
            'person_email' => $request->person_email,

            'subject' => $request->subject,
            'note' => $request->note,
            'expected_value' => (float)($request->expected_value ?? 0),
            'existing_machine' => $request->existing_machine,

            'next_followup_at' => $request->next_followup_at,
            'next_action_type' => $request->next_action_type,
            'last_activity_at' => now(),

            'lead_state' => $request->lead_state,
            'closed_at' => $request->lead_state === 'closed' ? now() : null,
            'lost_reason' => $request->lost_reason,
        ];

        // auto-fill from selected contact (still allowed override by typing after selection)
        $data = $this->applyContactAutofill($data, $request->organization_contact_id);

        $lead->update($data);

        return response()->json(['status'=>true,'message'=>'Lead updated successfully']);
    }

    public function destroy($id)
    {
        $u = Auth::user();
        $lead = Lead::findOrFail($id);
        $this->ensureLeadAccess($lead);

        $lead->delete();
        return response()->json(['status'=>true,'message'=>'Lead deleted successfully']);
    }

    // ✅ Organization dropdown
    public function orgOptions(Request $request)
    {
        $q = CrmAccess::applyOrganizationScope(Organization::query())->where('status','active');

        if($request->filled('q')){
            $s = trim($request->q);
            $q->where(function($qq) use ($s){
                $qq->where('name','like',"%{$s}%")->orWhere('phone_primary','like',"%{$s}%");
            });
        }

        return $q->orderBy('name')->limit(50)->get(['id','name']);
    }

    // ✅ Contacts by organization
    public function orgContacts($id)
    {
        CrmAccess::ensureOrganizationAllowed(Organization::findOrFail($id));
        $q = OrganizationContact::where('organization_id', $id);

        // if your contacts table has status column:
        if (\Schema::hasColumn('organization_contacts', 'status')) {
            $q->where('status','active');
        }

        return $q->orderBy('name')->get(['id','name','email','phone']);
    }

    // ✅ Contact details for autofill
    public function contactDetails($id)
    {
        $c = OrganizationContact::findOrFail($id);
        CrmAccess::ensureOrganizationAllowed(Organization::findOrFail($c->organization_id));
        return response()->json(['status'=>true,'data'=>$c]);
    }

    // ✅ Activities list
    public function activities($id)
    {
        $u = Auth::user();
        $lead = Lead::findOrFail($id);
        $this->ensureLeadAccess($lead);

        return response()->json(['status'=>true,'data'=>$lead->activities()->get()]);
    }

    // ✅ Add activity + snapshot update
    public function storeActivity(Request $request, $id)
    {
        $u = Auth::user();
        $lead = Lead::findOrFail($id);
        $this->ensureLeadAccess($lead);

        $request->validate([
            'activity_type' => 'required|string|max:30',
            'activity_text' => 'nullable|string',
            'activity_at' => 'nullable|date',
            'outcome_status' => 'nullable|string|max:50',
            'next_followup_at' => 'nullable|date',
            'next_action_type' => 'nullable|string|max:30',
        ]);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'activity_type' => $request->activity_type,
            'activity_text' => $request->activity_text,
            'activity_at' => CrmAccess::isStaff($u) ? now() : ($request->activity_at ?? now()),
            'outcome_status' => $request->outcome_status,
            'next_followup_at' => $request->next_followup_at,
            'next_action_type' => $request->next_action_type,
            'created_by' => $u->id,
        ]);

        $lead->update([
            'next_followup_at' => $request->next_followup_at,
            'next_action_type' => $request->next_action_type,
            'last_activity_at' => now(),
        ]);

        return response()->json(['status'=>true,'message'=>'Activity added successfully']);
    }
}