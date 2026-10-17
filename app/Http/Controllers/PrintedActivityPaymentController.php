<?php
namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PrintedActivityPaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:report.activity.view');
        $this->middleware('permission:report.view_all|report.view_branch|report.view_self');
        $this->middleware('permission:activity.payment.manage');
    }

    public function store(Request $request)
    {
        $data = $request->validate(['token'=>'required|uuid','confirmed'=>'required|accepted']);
        $key = 'printed-activity:'.$data['token'];
        $snapshot = Cache::get($key);
        abort_unless($snapshot && (int)$snapshot['user_id'] === (int)auth()->id(), 419, 'Printed report expired. Open the report again.');
        $updated = DB::transaction(function () use ($snapshot) {
            $updated = 0;
            // Lock and recheck current eligibility, avoiding races with rejection/payment.
            foreach (array_chunk($snapshot['ids'], 500) as $ids) {
                $query = Activity::whereIn('id', $ids);
                if (!auth()->user()->can('report.view_all')) {
                    $query->where('branch_id', auth()->user()->branch_id)->where('created_by', auth()->id());
                }
                foreach ($query->lockForUpdate()->get() as $activity) {
                    if (strtolower((string)$activity->status) === 'rejected' || $activity->payment_status !== 'waiting_for_payment') continue;
                    $activity->payment_status = 'paid';
                    $activity->paid_at = now('Asia/Dhaka');
                    $activity->paid_by = auth()->id();
                    $activity->save();
                    $updated++;
                }
            }
            return $updated;
        });
        Cache::forget($key);
        return response()->json(['updated'=>$updated,'skipped'=>count($snapshot['ids'])-$updated,
            'message'=>$updated.' record(s) marked Paid. '.(count($snapshot['ids'])-$updated).' skipped (ineligible or no longer accessible).']);
    }
}
