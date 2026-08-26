<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ProductSubcategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:product_subcategory.manage');
    }
    public function index()
    {
        $categories = ProductCategory::where('status','active')->orderBy('name')->get();
        return view('backend.content.products.subcategories.index', compact('categories'));
    }

    public function datatable(Request $request)
    {
        $q = ProductSubcategory::query()
            ->with('category:id,name')
            ->latest();

        // optional filter by category
        if($request->filled('category_id')){
            $q->where('category_id', $request->category_id);
        }

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('category', fn($row) => $row->category?->name ?? '-')
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
                    <button class="btn btn-sm btn-primary btn-edit mb-3 mr-3" data-id="'.$row->id.'"><i class="feather-edit"></i></button>
                    <button class="btn btn-sm btn-danger btn-delete mb-3" data-id="'.$row->id.'"><i class="feather-trash-2"></i></button>
                ';
            })
            ->rawColumns(['image','status','action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:product_categories,id',
            'name'        => 'required|string|max:255',
            'status'      => 'required|in:active,inactive',
            'image'       => 'nullable|image|max:2048',
        ]);

        // unique in same category
        if(ProductSubcategory::where('category_id',$request->category_id)->where('name',$request->name)->exists()){
            return response()->json(['status'=>false,'message'=>'Same name already exists in this category'], 422);
        }

        $sub = new ProductSubcategory();
        $sub->category_id = $request->category_id;
        $sub->name = $request->name;
        $sub->status = $request->status;

        // ✅ image store (your pattern)
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $path = 'public/images/product_subcategories/';
            $image->move($path, $name);
            $sub->image = $path . $name;
        }

        $sub->save();

        return response()->json(['status'=>true,'message'=>'Subcategory created successfully']);
    }

    public function show($id)
    {
        $data = ProductSubcategory::findOrFail($id);
        return response()->json(['status'=>true,'data'=>$data]);
    }

    public function update(Request $request, $id)
    {
        $sub = ProductSubcategory::findOrFail($id);

        $request->validate([
            'category_id' => 'required|exists:product_categories,id',
            'name'        => 'required|string|max:255',
            'status'      => 'required|in:active,inactive',
            'image'       => 'nullable|image|max:2048',
        ]);

        // unique in same category (excluding self)
        if(ProductSubcategory::where('category_id',$request->category_id)
            ->where('name',$request->name)
            ->where('id','!=',$sub->id)
            ->exists()){
            return response()->json(['status'=>false,'message'=>'Same name already exists in this category'], 422);
        }

        $sub->category_id = $request->category_id;
        $sub->name = $request->name;
        $sub->status = $request->status;

        // ✅ image store (your pattern)
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $path = 'public/images/product_subcategories/';
            $image->move($path, $name);
            $sub->image = $path . $name;
        }

        $sub->save();

        return response()->json(['status'=>true,'message'=>'Subcategory updated successfully']);
    }

    public function destroy($id)
    {
        $sub = ProductSubcategory::findOrFail($id);
        $sub->delete();

        return response()->json(['status'=>true,'message'=>'Subcategory deleted successfully']);
    }
}
