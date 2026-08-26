<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use Yajra\DataTables\DataTables;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:business.manage');
    }

    // ===================== INDEX =====================
    public function index()
    {
        return view('backend.content.departments.index');
    }

    // ===================== DATATABLE =====================
    public function datatable(Request $request)
    {
        $query = Department::query()->latest();

        return DataTables::of($query)

            ->addIndexColumn()

            ->addColumn('status_badge', function ($row) {
                return $row->status == 'active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })

            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-sm btn-info btn-edit" data-id="'.$row->id.'">Edit</button>
                    <button class="btn btn-sm btn-danger btn-delete" data-id="'.$row->id.'">Delete</button>
                ';
            })

            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    // ===================== STORE =====================
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        Department::create([
            'title' => $request->title,
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Department created successfully'
        ]);
    }

    // ===================== SHOW (EDIT DATA) =====================
    public function show($id)
    {
        $data = Department::findOrFail($id);

        return response()->json([
            'data' => $data
        ]);
    }

    // ===================== UPDATE =====================
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $dep = Department::findOrFail($id);

        $dep->update([
            'title' => $request->title,
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Department updated successfully'
        ]);
    }

    // ===================== DELETE =====================
    public function destroy($id)
    {
        $dep = Department::findOrFail($id);
        $dep->delete();

        return response()->json([
            'message' => 'Department deleted successfully'
        ]);
    }
}