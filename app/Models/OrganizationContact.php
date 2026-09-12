<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationContact extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'title','name','email','phone','phone_two','address',
        'phone_numbers','email_addresses','image_url','designation_id','department_id','additional_info','is_primary','status','created_by'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'phone_numbers' => 'array',
        'email_addresses' => 'array',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    
    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }
}
