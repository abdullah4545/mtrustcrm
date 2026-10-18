<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityTravel extends Model
{
    protected $table = 'activity_travels';

    protected $fillable = ['bill_review_version','deduction_amount','bill_status','deduction_reason','bill_reviewed_by','bill_reviewed_at','activity_id','tour_type','from_location','to_location','vehicle','distance','cost','image_url','entry_at','edit_count','last_edited_by','last_edited_at'];
    protected $casts = ['bill_review_version'=>'integer','deduction_amount'=>'decimal:2','bill_reviewed_at'=>'datetime','distance'=>'decimal:2','cost'=>'decimal:2','entry_at'=>'datetime','edit_count'=>'integer','last_edited_at'=>'datetime'];
    public function billReviewer(){ return $this->belongsTo(User::class,'bill_reviewed_by'); }
    public function activity(){ return $this->belongsTo(Activity::class); }
    public function lastEditor(){ return $this->belongsTo(User::class,'last_edited_by'); }
}
