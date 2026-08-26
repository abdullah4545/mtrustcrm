<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\District;
use App\Models\Upazila;
use App\Models\User;
use App\Models\UserAreaAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:user.view_all_branches|user.view_branch')->only(['index','datatable','show']);
        $this->middleware('permission:user.create')->only(['store']);
        $this->middleware('permission:user.edit')->only(['update']);
        $this->middleware('permission:user.delete')->only(['destroy']);
        $this->middleware('permission:user.role.assign')->only(['store','update']);
    }

    public function index()
    {
        return view('backend.content.users.index', [
            'branches' => Branch::orderBy('branch_name')->get(['id','branch_name','branch_code']),
            'roles' => Role::whereIn('name', $this->assignableRoles())->orderBy('name')->get(['name']),
            'districts' => District::where('is_active',1)->orderBy('name')->get(['id','name','division_id']),
        ]);
    }

    private function applyUserVisibility($query)
    {
        $auth = auth()->user();
        if ($auth->can('user.view_all_branches')) return $query;
        return $query->where('branch_id', $auth->branch_id);
    }

    public function datatable(Request $request)
    {
        $q = $this->applyUserVisibility(User::query())
            ->with(['branch:id,branch_name,branch_code','areaAssignments.district:id,name','areaAssignments.upazila:id,name'])
            ->latest();

        if ($request->filled('branch_id')) $q->where('branch_id', $request->branch_id);
        if ($request->filled('status')) $q->where('status', (int)$request->status);
        if ($request->filled('role')) $q->role($request->role);

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('branch', fn($r) => $r->branch?->branch_name ?? '-')
            ->addColumn('role', fn($r) => '<span class="badge bg-dark">'.e($r->getRoleNames()->first() ?? 'No role').'</span>')
            ->addColumn('areas', function($r){
                if (!$r->hasRole('staff')) return '<span class="text-muted">All permitted data</span>';
                if ($r->areaAssignments->isEmpty()) return '<span class="badge bg-danger">No area assigned</span>';
                $groups = $r->areaAssignments->groupBy('district_id');
                $parts = [];
                foreach ($groups as $rows) {
                    $district = $rows->first()->district?->name ?? 'District';
                    if ($rows->contains(fn($x) => is_null($x->upazila_id))) $parts[] = '<b>'.e($district).'</b>: All Upazilas';
                    else $parts[] = '<b>'.e($district).'</b>: '.e($rows->pluck('upazila.name')->filter()->join(', '));
                }
                return implode('<br>', $parts);
            })
            ->addColumn('profile', fn($r) => $r->profile ? '<img src="'.asset($r->profile).'" style="height:38px;width:38px;border-radius:50%;object-fit:cover">' : '<div class="avatar-text bg-soft-primary text-primary">'.e(strtoupper(substr($r->name,0,1))).'</div>')
            ->editColumn('status', fn($r) => $r->status ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>')
            ->addColumn('action', function($r){
                $html = '<div class="d-flex gap-1 flex-wrap">';
                if (auth()->user()->can('user.edit')) $html .= '<button class="btn btn-sm btn-primary btn-edit" data-id="'.$r->id.'"><i class="feather-edit"></i></button>';
                if (auth()->user()->can('user.delete') && (int)$r->id !== (int)auth()->id()) $html .= '<button class="btn btn-sm btn-outline-danger btn-delete" data-id="'.$r->id.'"><i class="feather-trash-2"></i></button>';
                return $html.'</div>';
            })
            ->rawColumns(['role','areas','profile','status','action'])
            ->make(true);
    }

    private function assignableRoles(): array
    {
        $auth = auth()->user();
        if ($auth->hasRole('superadmin')) {
            return Role::pluck('name')->all();
        }
        if ($auth->hasRole('admin')) {
            return Role::where('name', '!=', 'superadmin')->pluck('name')->all();
        }
        if ($auth->hasRole('branch_manager')) {
            return ['staff'];
        }
        return [];
    }

    private function ensureRoleAssignable(string $role): void
    {
        abort_unless(in_array($role, $this->assignableRoles(), true), 403, 'You are not allowed to assign this role.');
    }

    private function validateBase(Request $request, ?User $user = null): array
    {
        $rules = [
            'name'=>'required|string|max:150',
            'email'=>['nullable','email','max:190', Rule::unique('users','email')->ignore($user?->id)],
            'phone'=>['required','string','max:30', Rule::unique('users','phone')->ignore($user?->id)],
            'branch_id'=>'required|exists:branches,id',
            'role'=>'required|exists:roles,name',
            'present_address'=>'nullable|string|max:255',
            'parmanent_address'=>'nullable|string|max:255',
            'join_date'=>'nullable|date',
            'status'=>'required|in:0,1',
            'profile'=>'nullable|image|max:2048',
            'areas'=>'nullable|string',
        ];
        if ($user) {
            $rules['password']='nullable|string|min:8|max:72|confirmed';
        } else {
            $rules['password']='required|string|min:8|max:72|confirmed';
        }
        return $request->validate($rules);
    }

    private function normalizeAreas(Request $request, string $role): array
    {
        if ($role !== 'staff') return [];
        $areas = json_decode($request->input('areas','[]'), true);
        if (!is_array($areas) || count($areas) === 0) {
            throw ValidationException::withMessages(['areas'=>'Staff user-এর জন্য অন্তত একটি District/Upazila assign করুন।']);
        }

        $normalized = [];
        foreach ($areas as $area) {
            $districtId = (int)($area['district_id'] ?? 0);
            $upazilas = $area['upazila_ids'] ?? [];
            $all = (bool)($area['all_upazilas'] ?? false);
            if (!$districtId || !District::whereKey($districtId)->exists()) continue;

            if ($all) {
                $normalized[] = ['district_id'=>$districtId,'upazila_id'=>null];
                continue;
            }
            $validIds = Upazila::where('district_id',$districtId)->whereIn('id', array_map('intval',(array)$upazilas))->pluck('id');
            foreach ($validIds as $upazilaId) $normalized[] = ['district_id'=>$districtId,'upazila_id'=>(int)$upazilaId];
        }
        if (!$normalized) throw ValidationException::withMessages(['areas'=>'Assigned district-এর জন্য All Upazila অথবা অন্তত একটি Upazila select করুন।']);
        return collect($normalized)->unique(fn($x)=>$x['district_id'].'-'.($x['upazila_id'] ?? 'all'))->values()->all();
    }

    private function syncAreas(User $user, array $areas): void
    {
        $user->areaAssignments()->delete();
        foreach ($areas as $area) $user->areaAssignments()->create($area);
    }

    private function ensureManageable(User $user): void
    {
        $auth = auth()->user();
        if (!$auth->can('user.view_all_branches')) abort_unless((int)$user->branch_id === (int)$auth->branch_id, 403);
        if (!$auth->hasRole('superadmin') && $user->hasRole('superadmin')) abort(403);
        if ($auth->hasRole('branch_manager') && !$user->hasRole('staff')) abort(403);
    }

    public function store(Request $request)
    {
        $data = $this->validateBase($request);
        $auth = auth()->user();
        $this->ensureRoleAssignable($data['role']);
        if (!$auth->can('user.view_all_branches')) $data['branch_id'] = $auth->branch_id;
        $areas = $this->normalizeAreas($request, $data['role']);

        $user = DB::transaction(function() use ($request,$data,$auth,$areas){
            $user = new User();
            $user->fill(collect($data)->only(['name','email','phone','present_address','parmanent_address','join_date','status','branch_id'])->all());
            $user->added_by = $auth->id;
            $user->password = Hash::make($data['password']);
            if ($request->hasFile('profile')) {
                $file=$request->file('profile'); $name=time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                $file->move(public_path('images/users'),$name); $user->profile='public/images/users/'.$name;
            }
            $user->save();
            $user->syncRoles([$data['role']]);
            $this->syncAreas($user,$areas);
            return $user;
        });
        return response()->json(['status'=>true,'message'=>'User and work area saved successfully','id'=>$user->id]);
    }

    public function show($id)
    {
        $user = User::with(['areaAssignments.district:id,name','areaAssignments.upazila:id,name'])->findOrFail($id);
        $this->ensureManageable($user);
        $areas = $user->areaAssignments->groupBy('district_id')->map(function($rows,$districtId){
            return [
                'district_id'=>(int)$districtId,
                'district_name'=>$rows->first()->district?->name,
                'all_upazilas'=>$rows->contains(fn($r)=>is_null($r->upazila_id)),
                'upazila_ids'=>$rows->whereNotNull('upazila_id')->pluck('upazila_id')->map(fn($x)=>(int)$x)->values(),
            ];
        })->values();
        return response()->json(['status'=>true,'data'=>$user,'role'=>$user->getRoleNames()->first(),'areas'=>$areas]);
    }

    public function update(Request $request,$id)
    {
        $user=User::findOrFail($id); $this->ensureManageable($user);
        $data=$this->validateBase($request,$user); $auth=auth()->user();
        $this->ensureRoleAssignable($data['role']);
        if (!$auth->can('user.view_all_branches')) $data['branch_id']=$auth->branch_id;
        $areas=$this->normalizeAreas($request,$data['role']);

        DB::transaction(function() use ($request,$data,$user,$areas){
            $user->fill(collect($data)->only(['name','email','phone','present_address','parmanent_address','join_date','status','branch_id'])->all());
            if (!empty($data['password'])) $user->password=Hash::make($data['password']);
            if ($request->hasFile('profile')) {
                $file=$request->file('profile'); $name=time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                $file->move(public_path('images/users'),$name); $user->profile='public/images/users/'.$name;
            }
            $user->save(); $user->syncRoles([$data['role']]); $this->syncAreas($user,$areas);
        });
        return response()->json(['status'=>true,'message'=>'User and work area updated successfully']);
    }

    public function destroy($id)
    {
        $user=User::findOrFail($id); $this->ensureManageable($user);
        abort_if((int)$user->id === (int)auth()->id(),422,'You cannot delete your own account.');
        $user->delete();
        return response()->json(['status'=>true,'message'=>'User deleted successfully']);
    }
}
