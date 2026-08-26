<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lead_no','branch_id','assigned_user_id',
        'organization_id','organization_contact_id',
        'platform_id','status_stage_id',
        'person_name','person_phone','person_email',
        'subject','existing_machine','note','expected_value',
        'next_followup_at','next_action_type','last_activity_at',
        'lead_state','closed_at','lost_reason','created_by'
    ];

    protected $casts = [
        'expected_value' => 'decimal:2',
        'next_followup_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function platform() { return $this->belongsTo(Platform::class); }
    public function statusStage() { return $this->belongsTo(StatusStage::class, 'status_stage_id'); }

    public function organization() { return $this->belongsTo(Organization::class); }
    public function organizationContact() { return $this->belongsTo(OrganizationContact::class, 'organization_contact_id'); }

    public function activities() { return $this->hasMany(LeadActivity::class)->latest('activity_at'); }
    public function assignedUser() { return $this->belongsTo(User::class, 'assigned_user_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}