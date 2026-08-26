<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class DesignationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:business.manage');
    }

    public function index()
    {
        return view('backend.content.designation.index');
    }

    public function datatable(Request $request)
    {
        $query = Designation::query();

        return DataTables::of($query)
            ->addIndexColumn()

            ->addColumn('status_badge', function ($row) {
                $class = $row->status == 'active' ? 'success' : 'danger';

                return '<span class="badge bg-'.$class.'">'
                    .ucfirst($row->status).
                    '</span>';
            })

            ->addColumn('action', function ($row) {
                return '
                    <div class="d-flex">
                        <button class="btn btn-sm btn-warning btn-edit" data-id="'.$row->id.'">Edit</button>
                        &nbsp;&nbsp;
                        <button class="btn btn-sm btn-danger btn-delete" data-id="'.$row->id.'">Delete</button>
                    </div>
                ';
            })

            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'  => 'required|string|max:255|unique:designations,title',
            'status' => 'nullable|in:active,inactive',
        ]);

        Designation::create([
            'title'  => $request->title,
            'status' => $request->status ?? 'active',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Designation Created Successfully',
        ]);
    }

    public function show($id)
    {
        $designation = Designation::findOrFail($id);

        return response()->json([
            'status' => true,
            'data'   => $designation,
        ]);
    }

    public function update(Request $request, $id)
    {
        $designation = Designation::findOrFail($id);

        $request->validate([
            'title'  => 'required|string|max:255|unique:designations,title,'.$id,
            'status' => 'required|in:active,inactive',
        ]);

        $designation->update([
            'title'  => $request->title,
            'status' => $request->status,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Designation Updated Successfully',
        ]);
    }

    public function destroy($id)
    {
        $designation = Designation::findOrFail($id);
        $designation->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Designation Deleted Successfully',
        ]);
    }
}