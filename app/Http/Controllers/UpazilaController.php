<?php

namespace App\Http\Controllers;

use App\Models\Upazila;
use App\Models\District;
use App\Models\Division; 
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
 
class UpazilaController extends Controller
{
    public function __construct()
    { 
        $this->middleware('permission:geo.view')->only(['index','datatable','show']);
 
        $this->middleware('permission:geo.manage')->only(['store','update','destroy']);
 
        $this->middleware(function ($request, $next) {
            if (auth()->user()?->can('geo.manage')) {
                return $next($request);
            } 
            abort_unless(auth()->user()?->can('geo.view'), 403);
            return $next($request);
        })->only(['index','datatable','show']);
    }
    public function index()
    { 
        $divisions = Division::select('id','name')->orderBy('name')->get();
        return view('backend.content.geo.upazilas.index', compact('divisions'));
    }

    // ✅ Division → District list for dropdown
    public function ajaxDistricts(Request $request)
    {
        $request->validate([
            'division_id' => 'required|exists:divisions,id'
        ]);

        $districts = District::select('id','name')
            ->where('division_id', $request->division_id)
            ->orderBy('name')
            ->get();

        return response()->json(['status'=>true,'data'=>$districts]);
    }

    public function datatable(Request $request)
    {
        $query = Upazila::query()
            ->with(['division:id,name','district:id,name'])
            ->select(['id','division_id','district_id','name','code','is_active','created_at']);

        if ($request->filled('division_id')) {
            $query->where('division_id', $request->division_id);
        }
        if ($request->filled('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('division', fn($row) => $row->division?->name ?? '-')
            ->addColumn('district', fn($row) => $row->district?->name ?? '-')
            ->editColumn('is_active', fn($row) =>
                $row->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>'
            )
            ->addColumn('action', function ($row) {
                return '
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-primary btn-edit" data-id="'.$row->id.'">Edit</button>
                        <button class="btn btn-sm btn-danger btn-delete" data-id="'.$row->id.'">Delete</button>
                    </div>
                ';
            })
            ->rawColumns(['is_active','action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'district_id' => 'required|exists:districts,id',
            'name'        => 'required|string|max:150',
            'code'        => 'nullable|string|max:20',
            'is_active'   => 'nullable|boolean',
        ]);

        // ✅ Safety: district must belong to division
        $ok = District::where('id', $data['district_id'])
            ->where('division_id', $data['division_id'])
            ->exists();

        if(!$ok){
            return response()->json(['status'=>false,'message'=>'Invalid district for selected division'], 422);
        }

        $data['is_active'] = (bool)($request->is_active ?? 0);

        Upazila::create($data);

        return response()->json(['status'=>true,'message'=>'Upazila created successfully']);
    }

    public function show(Upazila $upazila)
    {
        return response()->json(['status'=>true,'data'=>$upazila]);
    }

    public function update(Request $request, Upazila $upazila)
    {
        $data = $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'district_id' => 'required|exists:districts,id',
            'name'        => 'required|string|max:150',
            'code'        => 'nullable|string|max:20',
            'is_active'   => 'nullable|boolean',
        ]);

        $ok = District::where('id', $data['district_id'])
            ->where('division_id', $data['division_id'])
            ->exists();

        if(!$ok){
            return response()->json(['status'=>false,'message'=>'Invalid district for selected division'], 422);
        }

        $data['is_active'] = (bool)($request->is_active ?? 0);

        $upazila->update($data);

        return response()->json(['status'=>true,'message'=>'Upazila updated successfully']);
    }

    public function destroy(Upazila $upazila)
    {
        $upazila->delete();
        return response()->json(['status'=>true,'message'=>'Upazila deleted successfully']);
    }
}
