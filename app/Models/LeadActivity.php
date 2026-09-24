<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadActivity extends Model
{
    protected $fillable = [
        'lead_id','activity_type','activity_text','activity_at',
        'outcome_status','next_followup_at','next_action_type','created_by','rescheduled_from_id'
    ];

    protected $casts = [
        'activity_at' => 'datetime',
        'next_followup_at' => 'datetime',
    ];

    public function lead() { return $this->belongsTo(Lead::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}