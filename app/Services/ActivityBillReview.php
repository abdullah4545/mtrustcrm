<?php
namespace App\Services;
use App\Models\Activity;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ActivityBillReview
{
    public static function cents($amount): int { return (int)round((float)$amount * 100); }
    public static function version($row): string {
        return hash('sha256', implode('|',[$row->id,$row->bill_review_version,$row->cost ?? $row->amount,$row->deduction_amount,$row->bill_status,$row->deduction_reason,$row->bill_reviewed_at?->format('Y-m-d H:i:s')]));
    }
    public static function deduction($claimed,string $decision,$amount): array {
        $claim=self::cents($claimed);
        $cut=$decision==='reject'?$claim:($decision==='approve'?0:self::cents($amount));
        if($cut<0 || $cut>$claim) throw ValidationException::withMessages(['deduction_amount'=>'Deduction must be between zero and the submitted amount.']);
        if($decision==='deduct' && $cut<=0) throw ValidationException::withMessages(['deduction_amount'=>'Enter an amount greater than zero.']);
        return [$cut/100,['approve'=>'approved','deduct'=>'deducted','reject'=>'rejected'][$decision]];
    }
    public static function hasHistory(Activity $a): bool { return DB::table('activity_bill_histories')->where('activity_id',$a->id)->exists(); }
    // Caller must hold the parent activity lock; ta/da/total are the amounts payable everywhere.
    public static function recalculate(Activity $a,bool $preserveLegacy = true): void {
        $travels=$a->travels()->get();$expenses=$a->expenses()->get();
        $ta=$travels->isNotEmpty() ? $travels->sum(fn($r)=>self::cents($r->cost)) : ($preserveLegacy ? self::cents($a->claimed_ta ?: $a->ta) : 0);
        $da=$expenses->isNotEmpty() ? $expenses->sum(fn($r)=>self::cents($r->amount)) : ($preserveLegacy ? self::cents($a->claimed_da ?: $a->da) : 0);
        $cutTa=$travels->sum(fn($r)=>self::cents($r->deduction_amount));
        $cutDa=$expenses->sum(fn($r)=>self::cents($r->deduction_amount));
        $rejected=$a->status==='rejected';
        $a->claimed_ta=$ta/100;$a->claimed_da=$da/100;$a->claimed_total=($ta+$da)/100;
        $a->ta=$rejected ? 0 : max(0,$ta-$cutTa)/100;
        $a->da=$rejected ? 0 : max(0,$da-$cutDa)/100;
        $a->total=($a->ta+$a->da);
        $a->deducted_total=$rejected ? ($ta+$da)/100 : ($cutTa+$cutDa)/100;
        $a->save();
    }
    public static function record(Activity $a,string $type,int $entryId,string $label,$claimed,$oldCut,$newCut,string $oldStatus,string $newStatus,string $reason): void {
        DB::table('activity_bill_histories')->insert([
            'activity_id'=>$a->id,'entry_type'=>$type,'entry_id'=>$entryId,'entry_label'=>mb_substr($label,0,500),
            'claimed_amount'=>$claimed,'old_deduction'=>$oldCut,'new_deduction'=>$newCut,'old_status'=>$oldStatus,'new_status'=>$newStatus,
            'reason'=>$reason,'reviewed_by'=>auth()->id(),'reviewer_name'=>auth()->user()->name,'created_at'=>now('Asia/Dhaka'),
        ]);
    }
    public static function activityDecision(Activity $a,string $status,string $reason): void {
        if ($a->payment_status==='paid') throw ValidationException::withMessages(['status'=>'Paid activities cannot be reviewed or adjusted.']);
        $reason=trim($reason);
        if ($status==='rejected' && $reason==='') throw ValidationException::withMessages(['review_note'=>'A rejection reason is required.']);
        $old=$a->status;$cut=$a->deducted_total;
        $a->status=$status;$a->review_note=$reason ?: null;$a->reviewed_by=auth()->id();$a->reviewed_at=now('Asia/Dhaka');
        if($status==='rejected'){$a->payment_status='unpaid';$a->paid_at=null;$a->paid_by=null;}
        self::recalculate($a);
        self::record($a,'activity',$a->id,'Whole activity',$a->claimed_total,$cut,$a->deducted_total,$old,$status,$reason ?: 'Activity approved');
    }
}
