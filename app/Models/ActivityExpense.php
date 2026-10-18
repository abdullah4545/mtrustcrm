<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityExpense extends Model
{
    protected $table = 'activity_expenses';

    protected $fillable = ['bill_review_version','deduction_amount','bill_status','deduction_reason','bill_reviewed_by','bill_reviewed_at','activity_id','expense_type_id','expense_type','amount','note','image_url','entry_at','edit_count','last_edited_by','last_edited_at'];
    protected $casts = ['bill_review_version'=>'integer','deduction_amount'=>'decimal:2','bill_reviewed_at'=>'datetime','amount'=>'decimal:2','entry_at'=>'datetime','edit_count'=>'integer','last_edited_at'=>'datetime'];
    public function billReviewer(){ return $this->belongsTo(User::class,'bill_reviewed_by'); }
    public function activity(){ return $this->belongsTo(Activity::class); }
    public function lastEditor(){ return $this->belongsTo(User::class,'last_edited_by'); }
    public function type(){ return $this->belongsTo(ExpenseType::class,'expense_type_id'); }
}
