<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class VehicleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:business.manage');
    }

    public function index()
    {
        return view('backend.content.vehicle.index');
    }

    public function datatable(Request $request)
    {
        $query = Vehicle::query()->latest();

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
            'title'  => 'required|string|max:255|unique:vehicles,title',
            'status' => 'nullable|in:active,inactive',
        ]);

        Vehicle::create([
            'title'  => $request->title,
            'status' => $request->status ?? 'active',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Vehicle Created Successfully',
        ]);
    }

    public function edit($id)
    {
        $vehicle = Vehicle::findOrFail($id);

        return response()->json([
            'status' => true,
            'data'   => $vehicle,
        ]);
    }

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $request->validate([
            'title'  => 'required|string|max:255|unique:vehicles,title,'.$id,
            'status' => 'required|in:active,inactive',
        ]);

        $vehicle->update([
            'title'  => $request->title,
            'status' => $request->status,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Vehicle Updated Successfully',
        ]);
    }

    public function destroy($id)
    {
        Vehicle::findOrFail($id)->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Vehicle Deleted Successfully',
        ]);
    }
}