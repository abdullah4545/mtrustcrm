<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_category_id',
        'organization_type_id',
        'name',
        'no_of_beds',
        'address',
        'division_id',
        'district_id',
        'upazila_id',
        'union_id',
        'phone_primary',
        'phone_secondary',
        'email',
        'website',
        'map_location_link',
        'dghs_facility_id',
        'latitude',
        'longitude',
        'notes',
        'about_us',
        'status',
        'created_by',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function category()
    {
        return $this->belongsTo(\App\Models\OrganizationCategory::class, 'organization_category_id');
    }

    public function type()
    {
        return $this->belongsTo(\App\Models\OrganizationType::class, 'organization_type_id');
    }

    public function division()
    {
        return $this->belongsTo(\App\Models\Division::class, 'division_id');
    }

    public function district()
    {
        return $this->belongsTo(\App\Models\District::class, 'district_id');
    }

    public function upazila()
    {
        return $this->belongsTo(\App\Models\Upazila::class, 'upazila_id');
    }

    public function union()
    {
        return $this->belongsTo(\App\Models\Union::class, 'union_id');
    }

    public function contacts()
    {
        return $this->hasMany(\App\Models\OrganizationContact::class)->orderBy('designation_id', 'asc');
    }

}
