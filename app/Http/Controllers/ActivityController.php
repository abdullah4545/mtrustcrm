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
        $this->middleware('permission:activity.approve')->only(['review']);
    }

    private function isAdmin(): bool
    {
        $u = Auth::user();
        return (bool) $u?->can('activity.approve');
    }

    private function isSuperAdmin(): bool
    {
        return (bool) Auth::user()?->hasRole('superadmin');
    }

    private function isLocked(Activity $activity): bool
    {
        return in_array(strtolower((string) $activity->status), ['approved', 'rejected'], true);
    }

    private function ensureUnlockedOrSuperAdmin(Activity $activity, string $action = 'change'): void
    {
        if (!$this->isLocked($activity) || $this->isSuperAdmin()) return;

        throw ValidationException::withMessages([
            'activity' => 'Approved or rejected activities are locked. Only Super Admin can '.$action.' them.',
        ]);
    }

    /**
     * Roles that may create an activity for another staff member and
     * manually choose the activity date/time. Regular staff always
     * create for themselves with the current system time.
     */
    private function canManageActivityEntry(): bool
    {
        return (bool) Auth::user()?->can('activity.create_for_others');
    }

    private function manageableStaffs()
    {
        $u = Auth::user();
        if (!$this->canManageActivityEntry()) return collect();

        $query = User::where('status', 1);
        if (!$u?->can('activity.view_all')) $query->where('branch_id', $u->branch_id);
        return $query->orderBy('name')->limit(300)->get(['id','name','branch_id']);
    }

    private function visibleQuery()
    {
        $u = Auth::user();
        $q = Activity::query()->with(['creator:id,name']);
        if ($u->can('activity.view_all')) return $q;
        if ($u->can('activity.view_branch')) return $q->where('branch_id',$u->branch_id);
        return $q->where('created_by',$u->id);
    }

    private function findVisible(int $id): Activity
    {
        return $this->visibleQuery()->with(['travels','expenses'])->findOrFail($id);
    }

    public function index(){
        $u = Auth::user();
        $staffs = User::where('status',1)
            ->when(!$u->can('activity.view_all'), fn($q) => $q->where('branch_id',$u->branch_id))
            ->orderBy('name')->get(['id','name']);
        return view('backend.content.activity.index', compact('staffs'));
    }

    public function datatable(Request $request)
    {
        $query = $this->visibleQuery()->latest('activity_at')->latest('id');
        if (Auth::user()->can('staff.filter') && $request->filled('created_by')) $query->where('created_by', $request->integer('created_by'));
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('staff_name', fn($row) => e($row->creator?->name ?? '-'))
            ->editColumn('date', fn($row) => optional($row->activity_at)->timezone('Asia/Dhaka')->format('d M Y, h:i A') ?? optional($row->date)->format('d M Y'))
            ->addColumn('status', fn($row) => '<span class="badge bg-'.($row->status==='approved'?'success':($row->status==='rejected'?'danger':'secondary')).'">'.e($row->status).'</span>')
            ->addColumn('action', function($row){
                $user = Auth::user();
                $locked = $this->isLocked($row);
                $superAdmin = $this->isSuperAdmin();
                $canModifyLocked = !$locked || $superAdmin;

                $html='<div class="d-flex gap-1 flex-wrap">';

                if ($user->can('activity.edit') && $canModifyLocked) {
                    $html.='<a href="'.route('activities.show',$row->id).'" class="btn btn-sm btn-primary">Edit</a>';
                }

                // Pending activities can be reviewed by normal approvers. Once reviewed,
                // only Super Admin may change the approval decision.
                if ($user->can('activity.approve') && (!$locked || $superAdmin)) {
                    if ($row->status !== 'approved') {
                        $html.='<button class="btn btn-sm btn-success btn-review" data-id="'.$row->id.'" data-status="approved">Approve</button>';
                    }
                    if ($row->status !== 'rejected') {
                        $html.='<button class="btn btn-sm btn-warning btn-review" data-id="'.$row->id.'" data-status="rejected">Reject</button>';
                    }
                }

                if ($user->can('activity.delete') && $canModifyLocked) {
                    $html.='<button class="btn btn-sm btn-danger btn-delete" data-id="'.$row->id.'">Delete</button>';
                }

                if ($locked && !$superAdmin) {
                    $html.='<span class="badge bg-light text-dark align-self-center"><i class="feather-lock"></i> Locked</span>';
                }

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

    public function organizationContacts(Request $request,$organization_id)
    {
        CrmAccess::ensureOrganizationAllowed(Organization::findOrFail($organization_id));
        $q = OrganizationContact::select('id','name','phone','designation_id','department_id')->with('designation:id,title')->where('organization_id',$organization_id)->where('status','active');
        if ($request->filled('department_id')) $q->where('department_id',$request->integer('department_id'));
        return response()->json($q->orderByDesc('is_primary')->orderBy('name')->get());
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'staff_id'=>'nullable|exists:users,id','activity_at'=>'nullable|date','organization_id'=>'required|exists:organizations,id','department'=>'nullable|string|max:255',
            'department_id'=>'nullable|exists:departments,id','contact_id'=>'nullable|exists:organization_contacts,id','contact_person'=>'nullable|string|max:255',
            'work_details'=>'nullable|string','remarks'=>'nullable|string','status'=>'nullable|in:pending,approved,rejected',
            'travels'=>'nullable|array','travels.*.from_location'=>'nullable|string|max:255','travels.*.to_location'=>'nullable|string|max:255',
            'travels.*.vehicle'=>'nullable|string|max:255','travels.*.distance'=>'nullable|numeric|min:0','travels.*.cost'=>'nullable|numeric|min:0',
            'travels.*.existing_image_url'=>'nullable|string|max:500','travels.*.image'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'expenses'=>'nullable|array','expenses.*.expense_type_id'=>'nullable|exists:expense_types,id','expenses.*.amount'=>'nullable|numeric|min:0','expenses.*.note'=>'nullable|string|max:500',
            'expenses.*.existing_image_url'=>'nullable|string|max:500','expenses.*.image'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);
    }

    private function storeActivityImage($file, string $group): ?string
    {
        if (!$file || !$file->isValid()) return null;
        $folder = 'uploads/activity/' . $group;
        $absolute = public_path($folder);
        if (!is_dir($absolute)) mkdir($absolute, 0775, true);
        $name = now('Asia/Dhaka')->format('YmdHis') . '_' . uniqid() . '.' . strtolower($file->getClientOriginalExtension());
        $file->move($absolute, $name);
        return 'public/' . $folder . '/' . $name;
    }

    private function deleteActivityImage(?string $storedPath): void
    {
        if (!$storedPath) return;
        $relative = preg_replace('#^/?public/#', '', str_replace('\\', '/', $storedPath));
        if (!str_starts_with($relative, 'uploads/activity/')) return;
        $absolute = public_path($relative);
        if (is_file($absolute)) @unlink($absolute);
    }

    private function ensureEditable(Activity $activity): void
    {
        // Approval is a hard lock. Having activity.edit or activity.approve is not enough;
        // only the protected superadmin role may override an approved/rejected activity.
        $this->ensureUnlockedOrSuperAdmin($activity, 'edit');

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

        $oldTravelImages = $activity->exists ? $activity->travels->pluck('image_url')->filter()->values()->all() : [];
        $oldExpenseImages = $activity->exists ? $activity->expenses->pluck('image_url')->filter()->values()->all() : [];

        return DB::transaction(function () use ($activity,$data,$org,$owner,$u,$travels,$expenses,$ta,$da,$now,$activityAt,$oldTravelImages,$oldExpenseImages) {
            $activity->fill([
                'organization_id'=>$org->id,'organization_name'=>$org->name,'department_id'=>$data['department_id']??null,'department'=>$data['department'],
                'contact_id'=>$data['contact_id']??null,'contact_person'=>$data['contact_person']??null,'work_details'=>$data['work_details']??null,
                'from_location'=>$travels->pluck('from_location')->filter()->implode(' | '),
                'to_location'=>$travels->pluck('to_location')->filter()->implode(' | '),
                'vehicle'=>$travels->pluck('vehicle')->filter()->implode(' | '),
                'distance'=>$travels->sum(fn($r)=>(float)($r['distance']??0)),
                'remarks'=>$data['remarks']??null,'ta'=>$ta,'da'=>$da,'total'=>$ta+$da,
                'status'=>'pending',
                'reviewed_by'=>null,'reviewed_at'=>null,'review_note'=>null,
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
            $keptTravelImages = [];
            foreach ($travels as $r) {
                $existing = $r['existing_image_url'] ?? null;
                $imagePath = ($existing && in_array($existing, $oldTravelImages, true)) ? $existing : null;
                if (!empty($r['image'])) {
                    $newPath = $this->storeActivityImage($r['image'], 'ta');
                    if ($newPath) $imagePath = $newPath;
                }
                if ($imagePath) $keptTravelImages[] = $imagePath;
                $activity->travels()->create([
                    'from_location'=>$r['from_location']??null,'to_location'=>$r['to_location']??null,'vehicle'=>$r['vehicle']??null,
                    'distance'=>(float)($r['distance']??0),'cost'=>(float)($r['cost']??0),'image_url'=>$imagePath,
                ]);
            }

            $activity->expenses()->delete();
            $keptExpenseImages = [];
            foreach ($expenses as $r) {
                $type = !empty($r['expense_type_id']) ? ExpenseType::find($r['expense_type_id']) : null;
                $existing = $r['existing_image_url'] ?? null;
                $imagePath = ($existing && in_array($existing, $oldExpenseImages, true)) ? $existing : null;
                if (!empty($r['image'])) {
                    $newPath = $this->storeActivityImage($r['image'], 'da');
                    if ($newPath) $imagePath = $newPath;
                }
                if ($imagePath) $keptExpenseImages[] = $imagePath;
                $activity->expenses()->create(['expense_type_id'=>$type?->id,'expense_type'=>$type?->name,'amount'=>(float)($r['amount']??0),'note'=>$r['note']??null,'image_url'=>$imagePath]);
            }

            foreach (array_diff($oldTravelImages, $keptTravelImages) as $oldImage) $this->deleteActivityImage($oldImage);
            foreach (array_diff($oldExpenseImages, $keptExpenseImages) as $oldImage) $this->deleteActivityImage($oldImage);

            return $activity->fresh(['travels','expenses','creator']);
        });
    }

    public function quickStore(Request $request){ $a=$this->saveActivity(new Activity(),$request); return response()->json(['status'=>true,'message'=>'Field activity created successfully','data'=>$a]); }
    public function store(Request $request){ $this->saveActivity(new Activity(),$request); return response()->json(['status'=>true,'message'=>'Field activity created successfully']); }
    public function organizations(Request $request){
        $term = trim((string)$request->get('q',''));
        $page = max(1,(int)$request->get('page',1));
        $perPage = 20;
        $q = CrmAccess::applyOrganizationScope(Organization::query())->where('status','active');
        if ($term !== '') $q->where(function($x) use($term){
            $x->where('name','like','%'.$term.'%')->orWhere('phone_primary','like','%'.$term.'%')->orWhere('email','like','%'.$term.'%');
        });
        $rows = $q->orderBy('name')->skip(($page-1)*$perPage)->take($perPage+1)->get(['id','name','phone_primary']);
        $more = $rows->count()>$perPage;
        return response()->json(['results'=>$rows->take($perPage)->map(fn($r)=>['id'=>$r->id,'text'=>$r->name.($r->phone_primary?' · '.$r->phone_primary:'')])->values(),'pagination'=>['more'=>$more]]);
    }
    public function departments(){ return response()->json(Department::select('id','title')->orderBy('title')->get()); }
    public function show($id){
        $activity=$this->findVisible((int)$id);
        $this->ensureUnlockedOrSuperAdmin($activity, 'edit');
        return view('backend.content.activity.edit',[
            'activity'=>$activity,
            'isAdmin'=>$this->isAdmin(),
            'canManageActivityEntry'=>$this->canManageActivityEntry(),
            'staffs'=>$this->manageableStaffs(),
            'expenseTypes'=>ExpenseType::where('status',1)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }
    public function update(Request $request,$id){ $a=$this->saveActivity($this->findVisible((int)$id),$request); return response()->json(['status'=>true,'message'=>'Field activity updated successfully','data'=>$a]); }
    public function review(Request $request, $id)
    {
        $data = $request->validate(['status'=>'required|in:approved,rejected','review_note'=>'nullable|string|max:500']);
        $activity = $this->findVisible((int)$id);
        $this->ensureUnlockedOrSuperAdmin($activity, 'change the approval status of');
        $activity->update([
            'status'=>$data['status'],
            'reviewed_by'=>Auth::id(),
            'reviewed_at'=>now('Asia/Dhaka'),
            'review_note'=>$data['review_note'] ?? null,
        ]);
        return response()->json(['status'=>true,'message'=>'Activity '.($data['status']==='approved'?'approved':'rejected').' successfully']);
    }

    public function destroy($id){
        $activity = $this->findVisible((int)$id);
        $this->ensureUnlockedOrSuperAdmin($activity, 'delete');
        $images = $activity->travels->pluck('image_url')->merge($activity->expenses->pluck('image_url'))->filter()->values();
        $activity->delete();
        $images->each(fn($path) => $this->deleteActivityImage($path));
        return response()->json(['status'=>true,'message'=>'Deleted']);
    }
}
