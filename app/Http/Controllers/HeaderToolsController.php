<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\User;
use App\Support\CrmAccess;
use Illuminate\Http\Request;

class HeaderToolsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $user = $request->user();
        $results = [];
        $like = '%'.$q.'%';

        if ($user->can('lead.view_all_branches') || $user->can('lead.view_branch') || $user->can('lead.view_self')) {
            $query = Lead::query()->where(function ($x) use ($like) {
                $x->where('lead_no', 'like', $like)->orWhere('person_name', 'like', $like)->orWhere('person_phone', 'like', $like);
            });
            if (CrmAccess::isStaff($user)) $query->where('assigned_user_id', $user->id);
            elseif ($user->can('lead.view_all_branches')) { /* all branches */ }
            elseif ($user->can('lead.view_branch')) $query->where('branch_id', $user->branch_id);
            else $query->where('assigned_user_id', $user->id);
            foreach ($query->latest()->limit(5)->get(['id','lead_no','person_name','person_phone']) as $row) {
                $results[] = ['type'=>'Lead','title'=>$row->person_name ?: $row->lead_no,'subtitle'=>trim(($row->lead_no ?: '').' · '.($row->person_phone ?: ''), ' ·'),'url'=>route('leads.show',$row->id),'icon'=>'feather-target'];
            }
        }

        if ($user->can('org.view')) {
            $query = CrmAccess::applyOrganizationScope(Organization::query(), $user)
                ->where(function ($x) use ($like) { $x->where('name','like',$like)->orWhere('phone_primary','like',$like); });
            foreach ($query->limit(5)->get(['id','name','phone_primary']) as $row) {
                $results[] = ['type'=>'Organization','title'=>$row->name,'subtitle'=>$row->phone_primary ?: 'Organization','url'=>route('org.contacts.index',$row->id),'icon'=>'feather-briefcase'];
            }
        }

        if ($user->can('product.view')) {
            foreach (Product::query()->where(function ($x) use ($like) { $x->where('name','like',$like)->orWhere('sku','like',$like); })->limit(5)->get(['id','name','sku']) as $row) {
                $results[] = ['type'=>'Product','title'=>$row->name,'subtitle'=>$row->sku ?: 'Product','url'=>route('products.index',['q'=>$row->sku ?: $row->name]),'icon'=>'feather-box'];
            }
        }

        if ($user->can('quotation.view_all_branches') || $user->can('quotation.view_branch') || $user->can('quotation.view_self')) {
            $query = Quotation::query()->where(function ($x) use ($like) { $x->where('quotation_no','like',$like)->orWhere('client_name','like',$like)->orWhere('client_phone','like',$like); });
            if (CrmAccess::isStaff($user)) $query->where('created_by',$user->id);
            elseif ($user->can('quotation.view_all_branches')) { /* all branches */ }
            elseif ($user->can('quotation.view_branch')) $query->where('branch_id',$user->branch_id);
            else $query->where('created_by',$user->id);
            foreach ($query->latest()->limit(4)->get(['id','quotation_no','client_name','client_phone']) as $row) {
                $results[] = ['type'=>'Quotation','title'=>$row->quotation_no,'subtitle'=>trim(($row->client_name ?: '').' · '.($row->client_phone ?: ''), ' ·'),'url'=>route('quotations.show',$row->id),'icon'=>'feather-file-text'];
            }
        }

        if ($user->can('sale.view_all_branches') || $user->can('sale.view_branch') || $user->can('sale.view_self')) {
            $query = Sale::query()->where(function ($x) use ($like) { $x->where('sale_no','like',$like)->orWhere('invoice_no','like',$like)->orWhere('client_name','like',$like)->orWhere('client_phone','like',$like); });
            if (CrmAccess::isStaff($user)) $query->where('sold_by',$user->id);
            elseif ($user->can('sale.view_all_branches')) { /* all branches */ }
            elseif ($user->can('sale.view_branch')) $query->where('branch_id',$user->branch_id);
            else $query->where('sold_by',$user->id);
            foreach ($query->latest()->limit(4)->get(['id','sale_no','invoice_no','client_name']) as $row) {
                $results[] = ['type'=>'Sale','title'=>$row->invoice_no ?: $row->sale_no,'subtitle'=>$row->client_name ?: 'Sale','url'=>route('sales.show',$row->id),'icon'=>'feather-shopping-cart'];
            }
        }

        if ($user->can('user.view_all_branches') || $user->can('user.view_branch')) {
            $query = User::query()->where('status',1)->where(function ($x) use ($like) { $x->where('name','like',$like)->orWhere('phone','like',$like)->orWhere('email','like',$like); });
            if (!$user->can('user.view_all_branches')) $query->where('branch_id',$user->branch_id);
            foreach ($query->limit(4)->get(['id','name','phone','email']) as $row) {
                $results[] = ['type'=>'Staff','title'=>$row->name,'subtitle'=>$row->phone ?: $row->email,'url'=>route('users.index',['q'=>$row->phone ?: $row->name]),'icon'=>'feather-user'];
            }
        }

        return response()->json(['results'=>array_slice($results, 0, 18)]);
    }

    public function notifications(Request $request)
    {
        $user = $request->user();
        $items = [];

        if ($user->can('lead.view_all_branches') || $user->can('lead.view_branch') || $user->can('lead.view_self')) {
            $query = Lead::query()->where('lead_state','open')->whereNotNull('next_followup_at');
            if (CrmAccess::isStaff($user)) $query->where('assigned_user_id',$user->id);
            elseif ($user->can('lead.view_all_branches')) { /* all branches */ }
            elseif ($user->can('lead.view_branch')) $query->where('branch_id',$user->branch_id);
            else $query->where('assigned_user_id',$user->id);

            $overdue = (clone $query)->where('next_followup_at','<',now()->startOfDay())->orderBy('next_followup_at')->limit(5)->get();
            foreach ($overdue as $lead) {
                $items[] = ['level'=>'danger','title'=>'Overdue follow-up','text'=>($lead->person_name ?: $lead->lead_no).' · '.$lead->next_followup_at->format('d M, h:i A'),'url'=>route('followups.index',['q'=>$lead->person_phone ?: $lead->lead_no]),'icon'=>'feather-alert-circle'];
            }

            $today = (clone $query)->whereBetween('next_followup_at',[now()->startOfDay(),now()->endOfDay()])->orderBy('next_followup_at')->limit(5)->get();
            foreach ($today as $lead) {
                $items[] = ['level'=>'primary','title'=>'Follow-up today','text'=>($lead->person_name ?: $lead->lead_no).' · '.$lead->next_followup_at->format('h:i A'),'url'=>route('followups.index',['q'=>$lead->person_phone ?: $lead->lead_no]),'icon'=>'feather-phone-call'];
            }
        }

        if (CrmAccess::isStaff($user) && $user->can('activity.create')) {
            $hasActivity = Activity::where('created_by',$user->id)->whereDate('date',now()->toDateString())->exists();
            if (!$hasActivity) {
                $items[] = ['level'=>'warning','title'=>'Activity not entered','text'=>'No activity has been entered for today.','url'=>route('activities.quick.create'),'icon'=>'feather-map-pin'];
            }
        }

        return response()->json(['count'=>count($items),'items'=>array_slice($items,0,10)]);
    }
}
