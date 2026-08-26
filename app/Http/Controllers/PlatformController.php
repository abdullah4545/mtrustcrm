<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PlatformController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:platform.manage');
    }
    
    public function index()
    {
        return view('backend.content.platforms.index');
    }

    public function datatable()
    {
        $q = Platform::query()->latest();

        return DataTables::of($q)
            ->addIndexColumn()
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
            ->rawColumns(['status','action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'  => 'required|string|max:150|unique:platforms,title',
            'status' => 'required|in:0,1',
        ]);

        Platform::create([
            'title'  => trim($request->title),
            'status' => (int)$request->status,
        ]);

        return response()->json(['status'=>true,'message'=>'Platform created successfully']);
    }

    public function show($id)
    {
        $p = Platform::findOrFail($id);
        return response()->json(['status'=>true,'data'=>$p]);
    }

    public function update(Request $request, $id)
    {
        $p = Platform::findOrFail($id);

        $request->validate([
            'title'  => 'required|string|max:150|unique:platforms,title,'.$p->id,
            'status' => 'required|in:0,1',
        ]);

        $p->update([
            'title'  => trim($request->title),
            'status' => (int)$request->status,
        ]);

        return response()->json(['status'=>true,'message'=>'Platform updated successfully']);
    }

    public function destroy($id)
    {
        $p = Platform::findOrFail($id);
        $p->delete();

        return response()->json(['status'=>true,'message'=>'Platform deleted successfully']);
    }
}