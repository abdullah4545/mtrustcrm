<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityTravel extends Model
{
    protected $table = 'activity_travels';

    protected $fillable = ['activity_id','from_location','to_location','vehicle','distance','cost','image_url','entry_at','edit_count','last_edited_by','last_edited_at'];
    protected $casts = ['distance'=>'decimal:2','cost'=>'decimal:2','entry_at'=>'datetime','edit_count'=>'integer','last_edited_at'=>'datetime'];
    public function activity(){ return $this->belongsTo(Activity::class); }
    public function lastEditor(){ return $this->belongsTo(User::class,'last_edited_by'); }
}
