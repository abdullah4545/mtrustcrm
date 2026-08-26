<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class BranchController extends Controller
{

    public function __construct()
    {
        // ✅ List / view
        $this->middleware('permission:branch.view_all|branch.manage')
            ->only(['index','datatable']);

        // ✅ Create/Update/Delete
        $this->middleware('permission:branch.manage')
            ->only(['store','show','update','destroy']);
    }
    public function index()
    {
        $divisions = DB::table('divisions')->select('id','name')->orderBy('name')->get();
        $parents   = Branch::orderBy('branch_name')->get(['id','branch_name','branch_code']);

        return view('backend.content.branch.index', compact('divisions','parents'));
    }

    public function datatable()
    {
        $q = Branch::query()
            ->with(['parent:id,branch_name,branch_code','division:id,name','district:id,name','upazila:id,name','union:id,name'])
            ->latest();

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('parent_branch', function($row){
                return $row->parent ? ($row->parent->branch_name.' ('.$row->parent->branch_code.')') : '-';
            })
            ->addColumn('geo', function($row){
                $d  = $row->division?->name ?? '—';
                $di = $row->district?->name ?? 'All Districts';
                $u  = $row->upazila?->name ?? 'All Upazilas';
                $un = $row->union?->name ?? 'All Unions';
                return $d.' / '.$di.' / '.$u.' / '.$un;
            })
            ->editColumn('is_main_branch', function($row){
                return $row->is_main_branch
                    ? '<span class="badge bg-info">Main</span>'
                    : '<span class="badge bg-secondary">Branch</span>';
            })
            ->editColumn('is_active', function($row){
                return $row->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('action', function($row){
                if (!auth()->user()->can('branch.manage')) return '<span class="text-muted">View only</span>';
                return '
                    <button class="btn btn-sm btn-primary btn-edit mb-2 mr-3" data-id="'.$row->id.'"><i class="feather-edit"></i></button>
                    <button class="btn btn-sm btn-danger btn-delete mb-2" data-id="'.$row->id.'"><i class="feather-trash-2"></i></button>
                ';
            })
            ->rawColumns(['is_main_branch','is_active','action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_code' => 'required|string|max:30|unique:branches,branch_code',
            'branch_name' => 'required|string|max:150',
            'parent_branch_id' => 'nullable|exists:branches,id',
            'is_main_branch' => 'required|in:0,1',
            'is_active' => 'required|in:0,1',

            'division_id' => 'required|exists:divisions,id',
            'district_id' => 'nullable|exists:districts,id',
            'upazila_id'  => 'nullable|exists:upazilas,id',
            'union_id'    => 'nullable|exists:unions,id',
        ]);

        // hierarchy validation
        if ($request->upazila_id && !$request->district_id) {
            return response()->json(['status'=>false,'message'=>'Upazila select করলে District লাগবে।'], 422);
        }
        if ($request->union_id && !$request->upazila_id) {
            return response()->json(['status'=>false,'message'=>'Union select করলে Upazila লাগবে।'], 422);
        }

        // belongs-to checks
        if ($request->district_id) {
            $ok = DB::table('districts')->where('id',$request->district_id)->where('division_id',$request->division_id)->exists();
            if (!$ok) return response()->json(['status'=>false,'message'=>'District টি এই Division এর অন্তর্ভুক্ত নয়।'], 422);
        }
        if ($request->upazila_id) {
            $ok = DB::table('upazilas')->where('id',$request->upazila_id)->where('district_id',$request->district_id)->exists();
            if (!$ok) return response()->json(['status'=>false,'message'=>'Upazila টি এই District এর অন্তর্ভুক্ত নয়।'], 422);
        }
        if ($request->union_id) {
            $ok = DB::table('unions')->where('id',$request->union_id)->where('upazila_id',$request->upazila_id)->exists();
            if (!$ok) return response()->json(['status'=>false,'message'=>'Union টি এই Upazila এর অন্তর্ভুক্ত নয়।'], 422);
        }

        // only one main branch rule (app level)
        if ((int)$request->is_main_branch === 1) {
            Branch::where('is_main_branch', 1)->update(['is_main_branch' => 0]);
        }

        $branch = Branch::create([
            'branch_code' => $request->branch_code,
            'branch_name' => $request->branch_name,
            'parent_branch_id' => $request->parent_branch_id,
            'is_main_branch' => (int)$request->is_main_branch,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'is_active' => (int)$request->is_active,

            'division_id' => $request->division_id,
            'district_id' => $request->district_id,
            'upazila_id' => $request->upazila_id,
            'union_id' => $request->union_id,
        ]);

        return response()->json(['status'=>true,'message'=>'Branch created successfully']);
    }

    public function show($id)
    {
        $b = Branch::findOrFail($id);
        return response()->json(['status'=>true,'data'=>$b]);
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $request->validate([
            'branch_code' => 'required|string|max:30|unique:branches,branch_code,'.$branch->id,
            'branch_name' => 'required|string|max:150',
            'parent_branch_id' => 'nullable|exists:branches,id',
            'is_main_branch' => 'required|in:0,1',
            'is_active' => 'required|in:0,1',

            'division_id' => 'required|exists:divisions,id',
            'district_id' => 'nullable|exists:districts,id',
            'upazila_id'  => 'nullable|exists:upazilas,id',
            'union_id'    => 'nullable|exists:unions,id',
        ]);

        if ($request->upazila_id && !$request->district_id) {
            return response()->json(['status'=>false,'message'=>'Upazila select করলে District লাগবে।'], 422);
        }
        if ($request->union_id && !$request->upazila_id) {
            return response()->json(['status'=>false,'message'=>'Union select করলে Upazila লাগবে।'], 422);
        }

        if ((int)$request->is_main_branch === 1) {
            Branch::where('is_main_branch', 1)->where('id','!=',$branch->id)->update(['is_main_branch' => 0]);
        }

        $branch->update([
            'branch_code' => $request->branch_code,
            'branch_name' => $request->branch_name,
            'parent_branch_id' => $request->parent_branch_id,
            'is_main_branch' => (int)$request->is_main_branch,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'is_active' => (int)$request->is_active,

            'division_id' => $request->division_id,
            'district_id' => $request->district_id,
            'upazila_id' => $request->upazila_id,
            'union_id' => $request->union_id,
        ]);

        return response()->json(['status'=>true,'message'=>'Branch updated successfully']);
    }

    public function destroy($id)
    {
        $branch = Branch::findOrFail($id);

        // optional safety: cannot delete main branch
        if ($branch->is_main_branch) {
            return response()->json(['status'=>false,'message'=>'Main branch cannot be deleted'], 422);
        }

        $branch->delete();
        return response()->json(['status'=>true,'message'=>'Branch deleted successfully']);
    }
}