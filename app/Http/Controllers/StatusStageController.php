<?php

namespace App\Http\Controllers;

use App\Models\StatusStage;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class StatusStageController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:status_stage.manage');
    }
    public function index()
    {
        return view('backend.content.status_stages.index');
    }

    public function datatable(Request $request)
    {
        $q = StatusStage::query()->latest();

        if ($request->filled('is_for')) {
            $q->where('is_for', $request->is_for);
        }

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('name_dot', function($row){
                return '<span style="display:inline-flex;align-items:center;gap:8px;">
                    <span style="width:10px;height:10px;border-radius:50%;background:'.$row->color.';"></span>
                    <span>'.e($row->name).'</span>
                </span>';
            })
            ->editColumn('is_for', fn($row) => '<span class="badge bg-dark">'.e($row->is_for).'</span>')
            ->editColumn('status', function($row){
                return $row->status
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('action', function($row){
                return '
                    <button class="btn btn-sm btn-primary btn-edit me-1" data-id="'.$row->id.'"><i class="feather-edit"></i></button>
                    <button class="btn btn-sm btn-danger btn-delete" data-id="'.$row->id.'"><i class="feather-trash-2"></i></button>
                ';
            })
            ->rawColumns(['name_dot','is_for','status','action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'is_for' => 'required|in:lead,sales,quotation',
            'name'   => 'required|string|max:120',
            'color'  => 'required|string|max:20',
            'status' => 'required|in:0,1',
        ]);

        // case-insensitive unique check
        $exists = StatusStage::where('is_for', $request->is_for)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->name))])
            ->exists();

        if($exists){
            return response()->json(['status'=>false,'message'=>'This name already exists for this module'], 422);
        }

        $row = StatusStage::create([
            'is_for' => $request->is_for,
            'name'   => trim($request->name),
            'color'  => $request->color,
            'status' => (int)$request->status,
        ]);

        return response()->json(['status'=>true,'message'=>'Created successfully','data'=>$row]);
    }

    public function show($id)
    {
        $row = StatusStage::findOrFail($id);
        return response()->json(['status'=>true,'data'=>$row]);
    }

    public function update(Request $request, $id)
    {
        $row = StatusStage::findOrFail($id);

        $request->validate([
            'is_for' => 'required|in:lead,sales,quotation',
            'name'   => 'required|string|max:120',
            'color'  => 'required|string|max:20',
            'status' => 'required|in:0,1',
        ]);

        $exists = StatusStage::where('is_for', $request->is_for)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->name))])
            ->where('id','!=',$row->id)
            ->exists();

        if($exists){
            return response()->json(['status'=>false,'message'=>'This name already exists for this module'], 422);
        }

        $row->update([
            'is_for' => $request->is_for,
            'name'   => trim($request->name),
            'color'  => $request->color,
            'status' => (int)$request->status,
        ]);

        return response()->json(['status'=>true,'message'=>'Updated successfully']);
    }

    public function destroy($id)
    {
        $row = StatusStage::findOrFail($id);
        $row->delete();

        return response()->json(['status'=>true,'message'=>'Deleted successfully']);
    }

    // ✅ dropdown options for lead/sales/quotation forms
    public function options($is_for)
    {
        return StatusStage::where('is_for', $is_for)
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id','name','color']);
    }
}