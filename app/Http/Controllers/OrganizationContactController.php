<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use App\Support\CrmAccess;

class OrganizationContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:org_contact.manage');
    }
    public function index(Organization $organization)
    {
        $organization->load(['contacts' => function($q){
            $q->orderByDesc('is_primary');
        }]);

        return view('backend.content.organization.contacts.index', compact('organization'));
    }

    public function datatable(Organization $organization)
    {
        CrmAccess::ensureOrganizationAllowed($organization);
        $q = OrganizationContact::where('organization_id', $organization->id)->latest();

        return DataTables::of($q)
            ->addIndexColumn()
            ->editColumn('is_primary', fn($row) => $row->is_primary ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>')
            ->editColumn('status', fn($row) => $row->status === 'active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>')
            ->addColumn('image', function($row){
                if(!$row->image_url) return '-';
                return '<img src="'.asset($row->image_url).'" style="height:35px;border-radius:6px">';
            })
            ->addColumn('action', function($row){
                return '
                <div class="d-flex">
                <button class="btn btn-sm btn-primary btn-edit" data-id="'.$row->id.'"><i class="feather-edit"></i></button>&nbsp;&nbsp;
                <button class="btn btn-sm btn-danger btn-delete" data-id="'.$row->id.'"><i class="feather-trash-2"></i></button>
                </div>
                ';
            })
            ->rawColumns(['is_primary','status','action','image'])
            ->make(true);
    }

    public function store(Request $request, Organization $organization)
    {
        CrmAccess::ensureOrganizationAllowed($organization);
        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|max:2048',
        ]);

        $contact = new OrganizationContact();
        $contact->organization_id = $organization->id;
        $contact->title = $request->title;
        $contact->name = $request->name;
        $contact->email = $request->email;
        $contact->phone = $request->phone;
        $contact->phone_two = $request->phone_two;
        $contact->address = $request->address;
        $contact->additional_info = $request->additional_info;
        $contact->is_primary = $request->is_primary ? 1 : 0;
        $contact->status = $request->status;
        $contact->created_by = Auth::id();

        // ✅ image store (your pattern)
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $path = 'public/images/organization_contacts/';
            $image->move($path, $name);
            $contact->image_url = $path . $name;
        }

        // ✅ if primary then others false
        if($contact->is_primary){
            OrganizationContact::where('organization_id', $organization->id)->update(['is_primary' => 0]);
        }

        $contact->save();

        return response()->json(['status'=>true,'message'=>'Contact created successfully']);
    }

    public function show(OrganizationContact $contact)
    {
        CrmAccess::ensureOrganizationAllowed(Organization::findOrFail($contact->organization_id));
        return response()->json(['status'=>true,'data'=>$contact]);
    }

    public function update(Request $request, OrganizationContact $contact)
    {
        CrmAccess::ensureOrganizationAllowed(Organization::findOrFail($contact->organization_id));
        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|max:2048',
        ]);

        $contact->title = $request->title;
        $contact->name = $request->name;
        $contact->email = $request->email;
        $contact->phone = $request->phone;
        $contact->phone_two = $request->phone_two;
        $contact->address = $request->address;
        $contact->additional_info = $request->additional_info;
        $contact->is_primary = $request->is_primary ? 1 : 0;
        $contact->status = $request->status;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $path = 'public/images/organization_contacts/';
            $image->move($path, $name);
            $contact->image_url = $path . $name;
        }

        if($contact->is_primary){
            OrganizationContact::where('organization_id', $contact->organization_id)
                ->where('id','!=',$contact->id)
                ->update(['is_primary'=>0]);
        }

        $contact->save();

        return response()->json(['status'=>true,'message'=>'Contact updated successfully']);
    }

    public function destroy(OrganizationContact $contact)
    {
        CrmAccess::ensureOrganizationAllowed(Organization::findOrFail($contact->organization_id));
        $contact->delete();
        return response()->json(['status'=>true,'message'=>'Contact deleted successfully']);
    }
}
