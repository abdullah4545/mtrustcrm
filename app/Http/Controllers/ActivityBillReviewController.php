<?php
namespace App\Http\Controllers;
use App\Models\Activity;
use App\Services\ActivityBillReview as Bills;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class ActivityBillReviewController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        $this->middleware('permission:activity.view_all|activity.view_branch|activity.view_self');
        $this->middleware('permission:activity.approve')->only('update');
    }
    private function query(){
        $q=Activity::query();
        if(!auth()->user()->can('activity.view_all'))$q->where('created_by',auth()->id());
        return $q;
    }
    public function show($id){
        $activity=$this->query()->with(['creator','travels.billReviewer','expenses.billReviewer'])->findOrFail($id);
        $history=DB::table('activity_bill_histories')->where('activity_id',$activity->id)->orderByDesc('id')->get();
        return view('backend.content.activity.bill-review',compact('activity','history'));
    }
    public function update(Request $request,$id){
        $data=$request->validate(['entry_type'=>'required|in:ta,da','entry_id'=>'required|integer','decision'=>'required|in:approve,deduct,reject',
            'deduction_amount'=>'nullable|numeric|min:0|decimal:0,2','reason'=>'required|string|max:2000','version'=>'required|string']);
        if(trim($data['reason'])==='')throw ValidationException::withMessages(['reason'=>'Please enter a reason.']);
        DB::transaction(function()use($data,$id){
            $a=$this->query()->lockForUpdate()->findOrFail($id);
            if($a->payment_status==='paid')throw ValidationException::withMessages(['decision'=>'Paid activities cannot be adjusted.']);
            if($a->status==='rejected')throw ValidationException::withMessages(['decision'=>'This activity is rejected. Restore its approval first.']);
            $row=($data['entry_type']==='ta'?$a->travels():$a->expenses())->lockForUpdate()->findOrFail($data['entry_id']);
            if(!hash_equals(Bills::version($row),$data['version']))throw ValidationException::withMessages(['version'=>'This entry changed. Reload the page before reviewing it.']);
            $claimed=$data['entry_type']==='ta'?$row->cost:$row->amount;
            [$cut,$state]=Bills::deduction($claimed,$data['decision'],$data['deduction_amount']??0);
            $label=$data['entry_type']==='ta'?($row->from_location.' → '.$row->to_location):($row->expense_type ?: 'DA');
            Bills::record($a,$data['entry_type'],$row->id,$label,$claimed,$row->deduction_amount,$cut,$row->bill_status,$state,trim($data['reason']));
            $row->deduction_amount=$cut;$row->bill_status=$state;$row->deduction_reason=trim($data['reason']);
            $row->bill_review_version=((int)$row->bill_review_version)+1;
            $row->bill_reviewed_by=auth()->id();$row->bill_reviewed_at=now('Asia/Dhaka');$row->save();
            // Changed payable amounts must pass through the payment queue again.
            $a->payment_status='unpaid';$a->paid_at=null;$a->paid_by=null;
            Bills::recalculate($a);
        });
        return back()->with('message','Bill entry reviewed. The original claim and full history are preserved.');
    }
}
