<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class BrandController extends Controller
{
    public function __construct()
    { 
        $this->middleware('permission:brand.manage')
            ->only(['index','datatable','store','show','update','destroy']);
    }
    public function index()
    {
        return view('backend.content.products.brands.index');
    }

    public function datatable(Request $request)
    {
        $q = Brand::query()->latest();

        return DataTables::of($q)
            ->addIndexColumn()
            ->editColumn('image', function($row){
                if(!$row->image) return '-';
                return '<img src="'.asset($row->image).'" style="height:40px;border-radius:8px">';
            })
            ->editColumn('status', function($row){
                return $row->status === 'active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('action', function($row){
                return '
                    <button class="btn btn-sm btn-primary btn-edit mb-2 mr-3" data-id="'.$row->id.'"><i class="feather-edit"></i></button>
                    <button class="btn btn-sm btn-danger btn-delete mb-2" data-id="'.$row->id.'"><i class="feather-trash-2"></i></button>
                ';
            })
            ->rawColumns(['image','status','action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255|unique:brands,name',
            'status' => 'required|in:active,inactive',
            'image'  => 'nullable|image|max:2048',
        ]);

        $brand = new Brand();
        $brand->name = $request->name;
        $brand->status = $request->status;

        // ✅ image store (your pattern)
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $path = 'public/images/brands/';
            $image->move($path, $name);
            $brand->image = $path . $name;
        }

        $brand->save();

        return response()->json(['status'=>true,'message'=>'Brand created successfully']);
    }

    public function show($id)
    {
        $data = Brand::findOrFail($id);
        return response()->json(['status'=>true,'data'=>$data]);
    }

    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $request->validate([
            'name'   => 'required|string|max:255|unique:brands,name,'.$brand->id,
            'status' => 'required|in:active,inactive',
            'image'  => 'nullable|image|max:2048',
        ]);

        $brand->name = $request->name;
        $brand->status = $request->status;

        // ✅ image store (your pattern)
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $path = 'public/images/brands/';
            $image->move($path, $name);
            $brand->image = $path . $name;
        }

        $brand->save();

        return response()->json(['status'=>true,'message'=>'Brand updated successfully']);
    }

    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();

        return response()->json(['status'=>true,'message'=>'Brand deleted successfully']);
    }
}
