<?php

namespace App\Http\Controllers;

use App\Models\Union as GeoUnion;
use App\Models\Division;
use App\Models\District;
use App\Models\Upazila;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UnionController extends Controller
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
        return view('backend.content.geo.unions.index', compact('divisions'));
    }

    // ✅ Division -> Districts
    public function ajaxDistricts(Request $request)
    {
        $request->validate(['division_id' => 'required|exists:divisions,id']);

        $districts = District::select('id','name')
            ->where('division_id', $request->division_id)
            ->orderBy('name')
            ->get();

        return response()->json(['status'=>true,'data'=>$districts]);
    }

    // ✅ District -> Upazilas
    public function ajaxUpazilas(Request $request)
    {
        $request->validate(['district_id' => 'required|exists:districts,id']);

        $upazilas = Upazila::select('id','name')
            ->where('district_id', $request->district_id)
            ->orderBy('name')
            ->get();

        return response()->json(['status'=>true,'data'=>$upazilas]);
    }

    public function datatable(Request $request)
    {
        $query = GeoUnion::query()
            ->with([
                'division:id,name',
                'district:id,name',
                'upazila:id,name',
            ])
            ->select(['id','division_id','district_id','upazila_id','name','code','is_active','created_at']);

        if ($request->filled('division_id')) $query->where('division_id', $request->division_id);
        if ($request->filled('district_id')) $query->where('district_id', $request->district_id);
        if ($request->filled('upazila_id')) $query->where('upazila_id', $request->upazila_id);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('division', fn($row) => $row->division?->name ?? '-')
            ->addColumn('district', fn($row) => $row->district?->name ?? '-')
            ->addColumn('upazila', fn($row) => $row->upazila?->name ?? '-')
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
            'upazila_id'  => 'required|exists:upazilas,id',
            'name'        => 'required|string|max:150',
            'code'        => 'nullable|string|max:20',
            'is_active'   => 'nullable|boolean',
        ]);

        // ✅ Validate chain: district belongs to division
        $ok1 = District::where('id', $data['district_id'])
            ->where('division_id', $data['division_id'])
            ->exists();

        if(!$ok1){
            return response()->json(['status'=>false,'message'=>'Invalid district for selected division'], 422);
        }

        // ✅ Validate chain: upazila belongs to district AND division
        $ok2 = Upazila::where('id', $data['upazila_id'])
            ->where('district_id', $data['district_id'])
            ->where('division_id', $data['division_id'])
            ->exists();

        if(!$ok2){
            return response()->json(['status'=>false,'message'=>'Invalid upazila for selected district/division'], 422);
        }

        $data['is_active'] = (bool)($request->is_active ?? 0);

        GeoUnion::create($data);

        return response()->json(['status'=>true,'message'=>'Union created successfully']);
    }

    public function show(GeoUnion $union)
    {
        return response()->json(['status'=>true,'data'=>$union]);
    }

    public function update(Request $request, GeoUnion $union)
    {
        $data = $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'district_id' => 'required|exists:districts,id',
            'upazila_id'  => 'required|exists:upazilas,id',
            'name'        => 'required|string|max:150',
            'code'        => 'nullable|string|max:20',
            'is_active'   => 'nullable|boolean',
        ]);

        $ok1 = District::where('id', $data['district_id'])
            ->where('division_id', $data['division_id'])
            ->exists();

        if(!$ok1){
            return response()->json(['status'=>false,'message'=>'Invalid district for selected division'], 422);
        }

        $ok2 = Upazila::where('id', $data['upazila_id'])
            ->where('district_id', $data['district_id'])
            ->where('division_id', $data['division_id'])
            ->exists();

        if(!$ok2){
            return response()->json(['status'=>false,'message'=>'Invalid upazila for selected district/division'], 422);
        }

        $data['is_active'] = (bool)($request->is_active ?? 0);

        $union->update($data);

        return response()->json(['status'=>true,'message'=>'Union updated successfully']);
    }

    public function destroy(GeoUnion $union)
    {
        $union->delete();
        return response()->json(['status'=>true,'message'=>'Union deleted successfully']);
    }
}
