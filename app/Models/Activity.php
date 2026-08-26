<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'date','activity_at','organization_id','organization_name','department_id','department','contact_id','contact_person',
        'details','from_location','to_location','distance','vehicle','work_details','ta','da','total','remarks',
        'created_by','entered_by','branch_id','status',
    ];

    protected $casts = [
        'date'=>'date','activity_at'=>'datetime','distance'=>'decimal:2','ta'=>'decimal:2','da'=>'decimal:2','total'=>'decimal:2',
    ];

    public function creator(){ return $this->belongsTo(User::class,'created_by'); }
    public function enteredBy(){ return $this->belongsTo(User::class,'entered_by'); }
    public function travels(){ return $this->hasMany(ActivityTravel::class); }
    public function expenses(){ return $this->hasMany(ActivityExpense::class); }
}
