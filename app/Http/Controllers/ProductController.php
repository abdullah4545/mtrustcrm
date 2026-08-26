<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Brand;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
 
        $this->middleware('permission:product.view|product.manage')->only([
            'index','datatable','show','subcategoriesByCategory','viewCatalogue','downloadCatalogue'
        ]);
 
        $this->middleware('permission:product.create|product.manage')->only(['store']);
 
        $this->middleware('permission:product.edit|product.update|product.manage')->only(['update']);
 
        $this->middleware('permission:product.delete|product.manage')->only(['destroy']);
    }
    public function index()
    {
        $categories = ProductCategory::where('status','active')->orderBy('name')->get();
        $brands     = Brand::where('status','active')->orderBy('name')->get();

        return view('backend.content.products.manage.index', compact('categories','brands'));
    }

    public function subcategoriesByCategory(Request $request)
    {
        $request->validate(['category_id'=>'required|integer']);

        $rows = ProductSubcategory::where('category_id', $request->category_id)
            ->where('status','active')
            ->orderBy('name')
            ->get(['id','name']);

        return response()->json(['status'=>true,'data'=>$rows]);
    }

    public function datatable(Request $request)
    {
        $q = Product::with(['category','subcategory','brand'])->latest();

        // ✅ filters (optional)
        if($request->filled('category_id')) $q->where('category_id',$request->category_id);
        if($request->filled('subcategory_id')) $q->where('subcategory_id',$request->subcategory_id);
        if($request->filled('brand_id')) $q->where('brand_id',$request->brand_id);
        if($request->filled('status')) $q->where('status',$request->status);

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('image', function($row){
                if(!$row->image_url) return '-';
                return '<img src="'.asset($row->image_url).'" style="height:40px;border-radius:8px">';
            })
            ->addColumn('catalogue', function($row){
                if(!$row->catalogue_file) return '<span class="text-muted">-</span>';
                $view = route('products.catalogue.view', $row->id);
                $download = route('products.catalogue.download', $row->id);
                return '<div class="d-flex gap-1"><a class="btn btn-sm btn-outline-primary" target="_blank" href="'.$view.'" title="View catalogue"><i class="feather-eye"></i></a><a class="btn btn-sm btn-outline-success" href="'.$download.'" title="Download catalogue"><i class="feather-download"></i></a></div>';
            })
            ->addColumn('cat', fn($row)=> $row->category?->name ?? '-')
            ->addColumn('subcat', fn($row)=> $row->subcategory?->name ?? '-')
            ->addColumn('brand', fn($row)=> $row->brand?->name ?? '-')
            ->editColumn('status', function($row){
                return $row->status === 'active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('action', function($row){
                $html = '<div class="d-flex gap-1">';
                if (auth()->user()->can('product.edit')) $html .= '<button class="btn btn-sm btn-primary btn-edit" data-id="'.$row->id.'"><i class="feather-edit"></i></button>';
                if (auth()->user()->can('product.delete')) $html .= '<button class="btn btn-sm btn-danger btn-delete" data-id="'.$row->id.'"><i class="feather-trash-2"></i></button>';
                return $html.'</div>';
            })
            ->rawColumns(['image','catalogue','status','action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|integer|exists:product_categories,id',
            'subcategory_id' => 'nullable|integer|exists:product_subcategories,id',
            'brand_id' => 'nullable|integer|exists:brands,id',

            'sku' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',

            'sale_price' => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'tax_rate' => 'nullable|numeric|min:0|max:100',

            'warranty_months' => 'nullable|integer|min:0|max:1200',
            'warranty_terms_details' => 'nullable|string',
            'configuration_description' => 'nullable|string',

            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|max:2048',
            'catalogue' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
        ]);

        $p = new Product();
        $p->category_id = $request->category_id;
        $p->subcategory_id = $request->subcategory_id;
        $p->brand_id = $request->brand_id;

        $p->sku = $request->sku;
        $p->name = $request->name;
        $p->description = $request->description;

        $p->sale_price = $request->sale_price;
        $p->purchase_price = $request->purchase_price;
        $p->vat_rate = $request->vat_rate ?? 0;
        $p->tax_rate = $request->tax_rate ?? 0;

        $p->warranty_months = $request->warranty_months;
        $p->warranty_terms_details = $request->warranty_terms_details;
        $p->configuration_description = $request->configuration_description;

        $p->status = $request->status;

        // ✅ image store (your pattern)
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $path = 'public/images/products/';
            if (!is_dir(public_path('images/products'))) mkdir(public_path('images/products'), 0775, true);
            $image->move(public_path('images/products'), $name);
            $p->image_url = $path . $name;
        }

        if ($request->hasFile('catalogue')) {
            $file = $request->file('catalogue');
            $name = time() . '_' . uniqid() . '.' . strtolower($file->getClientOriginalExtension());
            if (!is_dir(public_path('catalogues/products'))) mkdir(public_path('catalogues/products'), 0775, true);
            $file->move(public_path('catalogues/products'), $name);
            $p->catalogue_file = 'public/catalogues/products/' . $name;
        }

        $p->save();

        return response()->json(['status'=>true,'message'=>'Product created successfully']);
    }

    public function show($id)
    {
        $p = Product::findOrFail($id);
        return response()->json(['status'=>true,'data'=>$p]);
    }

    public function update(Request $request, $id)
    {
        $p = Product::findOrFail($id);

        $request->validate([
            'category_id' => 'required|integer|exists:product_categories,id',
            'subcategory_id' => 'nullable|integer|exists:product_subcategories,id',
            'brand_id' => 'nullable|integer|exists:brands,id',

            'sku' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',

            'sale_price' => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'tax_rate' => 'nullable|numeric|min:0|max:100',

            'warranty_months' => 'nullable|integer|min:0|max:1200',
            'warranty_terms_details' => 'nullable|string',
            'configuration_description' => 'nullable|string',

            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|max:2048',
            'catalogue' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
        ]);

        $p->fill($request->only([
            'category_id','subcategory_id','brand_id','sku','name','description',
            'sale_price','purchase_price','vat_rate','tax_rate',
            'warranty_months','warranty_terms_details','configuration_description','status'
        ]));

        // ✅ image store (your pattern)
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $path = 'public/images/products/';
            if (!is_dir(public_path('images/products'))) mkdir(public_path('images/products'), 0775, true);
            $image->move(public_path('images/products'), $name);
            $p->image_url = $path . $name;
        }

        if ($request->hasFile('catalogue')) {
            if ($p->catalogue_file) {
                $old = public_path(preg_replace('#^public/#', '', $p->catalogue_file));
                if (is_file($old)) @unlink($old);
            }
            $file = $request->file('catalogue');
            $name = time() . '_' . uniqid() . '.' . strtolower($file->getClientOriginalExtension());
            if (!is_dir(public_path('catalogues/products'))) mkdir(public_path('catalogues/products'), 0775, true);
            $file->move(public_path('catalogues/products'), $name);
            $p->catalogue_file = 'public/catalogues/products/' . $name;
        }

        $p->save();

        return response()->json(['status'=>true,'message'=>'Product updated successfully']);
    }

    private function catalogueAbsolutePath(Product $product): string
    {
        abort_unless($product->catalogue_file, 404, 'Catalogue not found.');
        $relative = preg_replace('#^/?public/#', '', str_replace('\\', '/', $product->catalogue_file));
        $absolute = public_path(ltrim($relative, '/'));
        abort_unless(is_file($absolute) && is_readable($absolute), 404, 'Catalogue file not found.');
        return $absolute;
    }

    public function viewCatalogue($id)
    {
        $product = Product::findOrFail($id);
        $path = $this->catalogueAbsolutePath($product);
        return response()->file($path, [
            'Content-Type' => mime_content_type($path) ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.basename($path).'"',
        ]);
    }

    public function downloadCatalogue($id)
    {
        $product = Product::findOrFail($id);
        $path = $this->catalogueAbsolutePath($product);
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $safeName = preg_replace('/[^A-Za-z0-9_-]+/', '-', $product->name) ?: 'product-catalogue';
        return response()->download($path, $safeName.'.'.$ext);
    }

    public function destroy($id)
    {
        $p = Product::findOrFail($id);
        $p->delete();
        return response()->json(['status'=>true,'message'=>'Product deleted successfully']);
    }
}
