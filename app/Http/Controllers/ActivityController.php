<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Department;
use App\Models\ExpenseType;
use App\Models\Organization;
use App\Models\OrganizationContact;
use App\Models\User;
use App\Models\Vehicle;
use App\Support\CrmAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\DataTables;

class ActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:activity.view_all|activity.view_branch|activity.view_self')
            ->only(['index','datatable','show','organizations','departments','vehicles','expenseTypes','staffs','organizationDepartments','organizationContacts']);
        $this->middleware('permission:activity.create')->only(['quickCreate','quickStore','store']);
        $this->middleware('permission:activity.edit')->only(['update']);
        $this->middleware('permission:activity.delete')->only(['destroy']);
    }

    private function isAdmin(): bool
    {
        $u = Auth::user();
        return (bool) $u?->hasAnyRole(['superadmin','admin']);
    }

    /**
     * Roles that may create an activity for another staff member and
     * manually choose the activity date/time. Regular staff always
     * create for themselves with the current system time.
     */
    private function canManageActivityEntry(): bool
    {
        return (bool) Auth::user()?->hasAnyRole(['superadmin','manager','accounts']);
    }

    private function manageableStaffs()
    {
        $u = Auth::user();
        if (!$this->canManageActivityEntry()) return collect();

        $query = User::role('staff')->where('status', 1);
        if ($u?->hasRole('accounts')) {
            $query->where('branch_id', $u->branch_id);
        }

        return $query->orderBy('name')->get(['id','name','branch_id']);
    }

    private function visibleQuery()
    {
        $u = Auth::user();
        $q = Activity::query()->with(['creator:id,name']);
        if (CrmAccess::isStaff($u)) return $q->where('created_by',$u->id);
        if ($u->can('activity.view_all')) return $q;
        if ($u->can('activity.view_branch')) return $q->where('branch_id',$u->branch_id);
        return $q->where('created_by',$u->id);
    }

    private function findVisible(int $id): Activity
    {
        return $this->visibleQuery()->with(['travels','expenses'])->findOrFail($id);
    }

    public function index(){ return view('backend.content.activity.index'); }

    public function datatable(Request $request)
    {
        $query = $this->visibleQuery()->latest('activity_at')->latest('id');
        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('date', fn($row) => optional($row->activity_at)->timezone('Asia/Dhaka')->format('d M Y, h:i A') ?? optional($row->date)->format('d M Y'))
            ->addColumn('status', fn($row) => '<span class="badge bg-'.($row->status==='approved'?'success':($row->status==='rejected'?'danger':'secondary')).'">'.e($row->status).'</span>')
            ->addColumn('action', function($row){
                $html='<div class="d-flex gap-1">';
                if (Auth::user()->can('activity.edit')) $html.='<a href="'.route('activities.show',$row->id).'" class="btn btn-sm btn-primary">Edit</a>';
                if (Auth::user()->can('activity.delete')) $html.='<button class="btn btn-sm btn-danger btn-delete" data-id="'.$row->id.'">Delete</button>';
                return $html.'</div>';
            })->rawColumns(['status','action'])->make(true);
    }

    public function quickCreate()
    {
        return view('backend.content.activity.quick_create', [
            'isAdmin' => $this->isAdmin(),
            'canManageActivityEntry' => $this->canManageActivityEntry(),
            'staffs' => $this->manageableStaffs(),
            'expenseTypes' => ExpenseType::where('status',1)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function vehicles(){ return response()->json(Vehicle::select('id','title')->where('status','active')->orderBy('title')->get()); }
    public function expenseTypes(){ return response()->json(ExpenseType::select('id','name')->where('status',1)->orderBy('sort_order')->orderBy('name')->get()); }
    public function staffs(){ abort_unless($this->canManageActivityEntry(),403); return response()->json($this->manageableStaffs()); }

    public function organizationDepartments($organization_id)
    {
        CrmAccess::ensureOrganizationAllowed(Organization::findOrFail($organization_id));
        return response()->json(OrganizationContact::with('department:id,title')->where('organization_id',$organization_id)->whereNotNull('department_id')->where('status','active')->get()->pluck('department')->filter()->unique('id')->values());
    }

    public function organizationContacts($organization_id,$department_id)
    {
        CrmAccess::ensureOrganizationAllowed(Organization::findOrFail($organization_id));
        return response()->json(OrganizationContact::select('id','name','phone','designation_id')->with('designation:id,title')->where('organization_id',$organization_id)->where('department_id',$department_id)->where('status','active')->orderByDesc('is_primary')->orderBy('name')->get());
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'staff_id'=>'nullable|exists:users,id','activity_at'=>'nullable|date','organization_id'=>'required|exists:organizations,id','department'=>'required|string|max:255',
            'department_id'=>'nullable|exists:departments,id','contact_id'=>'nullable|exists:organization_contacts,id','contact_person'=>'nullable|string|max:255',
            'work_details'=>'nullable|string','remarks'=>'nullable|string','status'=>'nullable|in:pending,approved,rejected',
            'travels'=>'nullable|array','travels.*.from_location'=>'nullable|string|max:255','travels.*.to_location'=>'nullable|string|max:255',
            'travels.*.vehicle'=>'nullable|string|max:255','travels.*.distance'=>'nullable|numeric|min:0','travels.*.cost'=>'nullable|numeric|min:0',
            'expenses'=>'nullable|array','expenses.*.expense_type_id'=>'nullable|exists:expense_types,id','expenses.*.amount'=>'nullable|numeric|min:0','expenses.*.note'=>'nullable|string|max:500',
        ]);
    }

    private function ensureEditable(Activity $activity): void
    {
        if ($this->isAdmin()) return;
        $activityDate = ($activity->activity_at ?? $activity->created_at ?? $activity->date)?->timezone('Asia/Dhaka')->toDateString();
        if ($activityDate !== now('Asia/Dhaka')->toDateString()) {
            throw ValidationException::withMessages(['activity'=>'Previous day activities can only be edited by Admin or Super Admin.']);
        }
    }

    private function saveActivity(Activity $activity, Request $request): Activity
    {
        if ($activity->exists) $this->ensureEditable($activity);
        $data = $this->validated($request);
        $u = Auth::user();
        $owner = $u;
        if ($this->canManageActivityEntry() && !empty($data['staff_id'])) {
            $allowedStaff = $this->manageableStaffs()->firstWhere('id', (int) $data['staff_id']);
            abort_unless($allowedStaff, 403, 'You cannot create an activity for this staff member.');
            $owner = User::findOrFail($allowedStaff->id);
        }

        $org = Organization::findOrFail($data['organization_id']);
        CrmAccess::ensureOrganizationAllowed($org, $owner);

        $travels = collect($data['travels'] ?? [])->filter(fn($r) => filled($r['from_location'] ?? null) || filled($r['to_location'] ?? null) || (float)($r['cost'] ?? 0)>0)->values();
        $expenses = collect($data['expenses'] ?? [])->filter(fn($r) => !empty($r['expense_type_id']) || (float)($r['amount'] ?? 0)>0)->values();

        $ta = $travels->sum(fn($r)=>(float)($r['cost']??0));
        $da = $expenses->sum(fn($r)=>(float)($r['amount']??0));
        $now = now('Asia/Dhaka');
        $activityAt = $now;
        if ($this->canManageActivityEntry() && !empty($data['activity_at'])) {
            $activityAt = \Carbon\Carbon::parse($data['activity_at'], 'Asia/Dhaka');
        } elseif ($activity->exists) {
            $activityAt = ($activity->activity_at ?? $activity->created_at ?? $now)->copy()->timezone('Asia/Dhaka');
        }

        return DB::transaction(function () use ($activity,$data,$org,$owner,$u,$travels,$expenses,$ta,$da,$now,$activityAt) {
            $activity->fill([
                'organization_id'=>$org->id,'organization_name'=>$org->name,'department_id'=>$data['department_id']??null,'department'=>$data['department'],
                'contact_id'=>$data['contact_id']??null,'contact_person'=>$data['contact_person']??null,'work_details'=>$data['work_details']??null,
                'from_location'=>$travels->pluck('from_location')->filter()->implode(' | '),
                'to_location'=>$travels->pluck('to_location')->filter()->implode(' | '),
                'vehicle'=>$travels->pluck('vehicle')->filter()->implode(' | '),
                'distance'=>$travels->sum(fn($r)=>(float)($r['distance']??0)),
                'remarks'=>$data['remarks']??null,'ta'=>$ta,'da'=>$da,'total'=>$ta+$da,
                'status'=>CrmAccess::isStaff($u)?'pending':($data['status']??$activity->status??'pending'),
            ]);

            if (!$activity->exists) {
                $activity->entered_by = $u->id;
            }

            if (!$activity->exists || $this->canManageActivityEntry()) {
                $activity->date = $activityAt->toDateString();
                $activity->activity_at = $activityAt;
            }

            if (!$activity->exists || $this->canManageActivityEntry()) {
                $activity->created_by = $owner->id;
                $activity->branch_id = $owner->branch_id ?? $u->branch_id;
            }
            $activity->save();

            $activity->travels()->delete();
            foreach ($travels as $r) $activity->travels()->create([
                'from_location'=>$r['from_location']??null,'to_location'=>$r['to_location']??null,'vehicle'=>$r['vehicle']??null,
                'distance'=>(float)($r['distance']??0),'cost'=>(float)($r['cost']??0),
            ]);

            $activity->expenses()->delete();
            foreach ($expenses as $r) {
                $type = !empty($r['expense_type_id']) ? ExpenseType::find($r['expense_type_id']) : null;
                $activity->expenses()->create(['expense_type_id'=>$type?->id,'expense_type'=>$type?->name,'amount'=>(float)($r['amount']??0),'note'=>$r['note']??null]);
            }
            return $activity->fresh(['travels','expenses','creator']);
        });
    }

    public function quickStore(Request $request){ $a=$this->saveActivity(new Activity(),$request); return response()->json(['status'=>true,'message'=>'Field activity created successfully','data'=>$a]); }
    public function store(Request $request){ $this->saveActivity(new Activity(),$request); return response()->json(['status'=>true,'message'=>'Field activity created successfully']); }
    public function organizations(){ return response()->json(CrmAccess::applyOrganizationScope(Organization::query())->select('id','name')->where('status','active')->orderBy('name')->get()); }
    public function departments(){ return response()->json(Department::select('id','title')->orderBy('title')->get()); }
    public function show($id){
        $activity=$this->findVisible((int)$id);
        return view('backend.content.activity.edit',[
            'activity'=>$activity,
            'isAdmin'=>$this->isAdmin(),
            'canManageActivityEntry'=>$this->canManageActivityEntry(),
            'staffs'=>$this->manageableStaffs(),
            'expenseTypes'=>ExpenseType::where('status',1)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }
    public function update(Request $request,$id){ $a=$this->saveActivity($this->findVisible((int)$id),$request); return response()->json(['status'=>true,'message'=>'Field activity updated successfully','data'=>$a]); }
    public function destroy($id){ $this->findVisible((int)$id)->delete(); return response()->json(['status'=>true,'message'=>'Deleted']); }
}
