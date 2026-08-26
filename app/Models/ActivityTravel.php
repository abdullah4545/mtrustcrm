<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityTravel extends Model
{
    protected $table = 'activity_travels';

    protected $fillable = ['activity_id','from_location','to_location','vehicle','distance','cost'];
    protected $casts = ['distance'=>'decimal:2','cost'=>'decimal:2'];
    public function activity(){ return $this->belongsTo(Activity::class); }
}
