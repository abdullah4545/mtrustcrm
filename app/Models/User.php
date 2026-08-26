<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $fillable = [
        'name','email','password',
        'phone','present_address','parmanent_address','profile',
        'join_date','last_login_at','status',
        'branch_id','added_by',
        'division_id','district_id','upazila_id','union_id'
    ];

    protected $casts = [
        'status' => 'boolean',
        'join_date' => 'date',
        'last_login_at' => 'datetime',
    ];

    public function branch(){ return $this->belongsTo(Branch::class); }
    public function creator(){ return $this->belongsTo(User::class,'added_by'); }

    public function division(){ return $this->belongsTo(Division::class); }
    public function district(){ return $this->belongsTo(District::class); }
    public function upazila(){ return $this->belongsTo(Upazila::class); }
    public function union(){ return $this->belongsTo(Union::class); }
    public function areaAssignments(){ return $this->hasMany(UserAreaAssignment::class); }
}