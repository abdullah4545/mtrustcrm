<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ProductCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:product_category.manage');
    }
    public function index()
    {
        return view('backend.content.products.categories.index');
    }

    public function datatable(Request $request)
    {
        $q = ProductCategory::query()->latest();

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
            'name'   => 'required|string|max:255|unique:product_categories,name',
            'status' => 'required|in:active,inactive',
            'image'  => 'nullable|image|max:2048',
        ]);

        $category = new ProductCategory();
        $category->name = $request->name;
        $category->status = $request->status;

        // ✅ image store (your pattern)
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $path = 'public/images/product_categories/';
            $image->move($path, $name);
            $category->image = $path . $name;
        }

        $category->save();

        return response()->json(['status'=>true,'message'=>'Category created successfully']);
    }

    public function show($id)
    {
        $data = ProductCategory::findOrFail($id);
        return response()->json(['status'=>true,'data'=>$data]);
    }

    public function update(Request $request, $id)
    {
        $category = ProductCategory::findOrFail($id);

        $request->validate([
            'name'   => 'required|string|max:255|unique:product_categories,name,'.$category->id,
            'status' => 'required|in:active,inactive',
            'image'  => 'nullable|image|max:2048',
        ]);

        $category->name = $request->name;
        $category->status = $request->status;

        // ✅ image store (your pattern)
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $path = 'public/images/product_categories/';
            $image->move($path, $name);
            $category->image = $path . $name;
        }

        $category->save();

        return response()->json(['status'=>true,'message'=>'Category updated successfully']);
    }

    public function destroy($id)
    {
        $category = ProductCategory::findOrFail($id);
        $category->delete();

        return response()->json(['status'=>true,'message'=>'Category deleted successfully']);
    }
}
