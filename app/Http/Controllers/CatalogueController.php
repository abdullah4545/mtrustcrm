<?php
namespace App\Http\Controllers;

use App\Models\Catalogue;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class CatalogueController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:catalogue.manage');
    }

    public function index()
    {
        return view('backend.content.products.catalogues.index', [
            'categories' => ProductCategory::orderBy('name')->get(['id','name']),
        ]);
    }

    public function datatable(Request $request)
    {
        $query = Catalogue::with('category:id,name')->select('catalogues.*');
        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
        if ($request->filled('status')) $query->where('status', $request->status);
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('file', function ($row) {
                $url = e(asset($row->file_path));
                $isPdf = strtolower(pathinfo($row->file_path, PATHINFO_EXTENSION)) === 'pdf';
                $preview = $isPdf
                    ? '<span class="badge bg-soft-danger text-danger p-3"><i class="feather-file-text me-1"></i>PDF</span>'
                    : '<img src="'.$url.'" alt="Catalogue image" loading="lazy" style="width:62px;height:62px;object-fit:cover;border-radius:8px">';
                return '<a href="'.$url.'" target="_blank" rel="noopener">'.$preview.'</a>';
            })
            ->editColumn('title', fn($row) => e($row->title))
            ->addColumn('category_name', fn($row) => e($row->category?->name ?? '—'))
            ->editColumn('description', fn($row) => e(Str::limit($row->description ?? '', 120)))
            ->editColumn('status', fn($row) => $row->status === 'active'
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-secondary">Inactive</span>')
            ->addColumn('action', fn($row) => '<div class="d-flex gap-2"><button type="button" class="btn btn-sm btn-primary catalogue-edit" data-id="'.$row->id.'" title="Edit"><i class="feather-edit"></i></button><button type="button" class="btn btn-sm btn-danger catalogue-delete" data-id="'.$row->id.'" title="Delete"><i class="feather-trash-2"></i></button></div>')
            ->rawColumns(['file','title','category_name','description','status','action'])
            ->make(true);
    }

    public function show($id)
    {
        $catalogue = Catalogue::findOrFail($id);
        return response()->json(['status'=>true, 'data'=>$catalogue, 'file_url'=>asset($catalogue->file_path)]);
    }

    private function validated(Request $request, bool $creating): array
    {
        return $request->validate([
            'title'=>'required|string|max:255',
            'category_id'=>'required|integer|exists:product_categories,id',
            'description'=>'nullable|string|max:50000',
            'file'=>[$creating ? 'required' : 'nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
            'status'=>'required|in:active,inactive',
        ]);
    }

    private function upload(Request $request): string
    {
        $directory = public_path('uploads/catalogues');
        File::ensureDirectoryExists($directory, 0775);
        $file = $request->file('file');
        $name = (string) Str::uuid().'.'.$file->extension();
        $file->move($directory, $name);
        // Same asset-path convention used throughout this CRM.
        return 'public/uploads/catalogues/'.$name;
    }

    private function deleteFile(?string $path): void
    {
        if (!$path || !preg_match('~^public/uploads/catalogues/[a-f0-9-]+\.(jpg|jpeg|png|webp|pdf)$~i', $path)) return;
        try {
            File::delete(public_path('uploads/catalogues/'.basename($path)));
        } catch (\Throwable $e) {
            report($e); // A cleanup failure must not report a successfully committed save as failed.
        }
    }

    private function saveFailure(\Throwable $e)
    {
        $reference = (string) Str::uuid();
        \Illuminate\Support\Facades\Log::error('Catalogue save failed', ['reference'=>$reference, 'exception'=>$e]);
        $message = $e instanceof \Symfony\Component\HttpFoundation\File\Exception\FileException
            ? 'The file could not be uploaded. Check upload folder permissions and available disk space.'
            : 'The catalogue could not be saved. Check that the catalogue database update has been applied. Ask the administrator to check the error reference.';
        return response()->json(['status'=>false, 'message'=>$message, 'error_reference'=>$reference], 500);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);
        unset($data['file']);
        $path = null;
        try {
            $data['file_path'] = $path = $this->upload($request);
            $data['created_by'] = $request->user()->id;
            Catalogue::create($data);
        } catch (\Throwable $e) {
            $this->deleteFile($path);
            return $this->saveFailure($e);
        }
        return response()->json(['status'=>true, 'message'=>'Catalogue created successfully.']);
    }

    public function update(Request $request, $id)
    {
        $catalogue = Catalogue::findOrFail($id);
        $data = $this->validated($request, false);
        unset($data['file']);
        $oldPath = $catalogue->file_path;
        $newPath = null;
        try {
            if ($request->hasFile('file')) $data['file_path'] = $newPath = $this->upload($request);
            $catalogue->update($data);
        } catch (\Throwable $e) {
            $this->deleteFile($newPath);
            return $this->saveFailure($e);
        }
        if ($newPath) $this->deleteFile($oldPath);
        return response()->json(['status'=>true, 'message'=>'Catalogue updated successfully.']);
    }

    public function destroy($id)
    {
        $catalogue = Catalogue::findOrFail($id);
        $path = $catalogue->file_path;
        try { $catalogue->delete(); }
        catch (\Throwable $e) { return $this->saveFailure($e); }
        $this->deleteFile($path);
        return response()->json(['status'=>true, 'message'=>'Catalogue deleted successfully.']);
    }
}
