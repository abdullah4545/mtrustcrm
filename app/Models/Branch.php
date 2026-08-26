<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'branch_code','branch_name','parent_branch_id','is_main_branch',
        'address','phone','email','is_active',
        'division_id','district_id','upazila_id','union_id',
    ];

    public function parent()
    {
        return $this->belongsTo(Branch::class, 'parent_branch_id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function upazila()
    {
        return $this->belongsTo(Upazila::class);
    }

    public function union()
    {
        return $this->belongsTo(Union::class);
    }
}