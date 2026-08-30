<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityExpense extends Model
{
    protected $table = 'activity_expenses';

    protected $fillable = ['activity_id','expense_type_id','expense_type','amount','note','image_url'];
    protected $casts = ['amount'=>'decimal:2'];
    public function activity(){ return $this->belongsTo(Activity::class); }
    public function type(){ return $this->belongsTo(ExpenseType::class,'expense_type_id'); }
}
