<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAreaAssignment extends Model
{
    protected $fillable = ['user_id','district_id','upazila_id'];

    public function user(){ return $this->belongsTo(User::class); }
    public function district(){ return $this->belongsTo(District::class); }
    public function upazila(){ return $this->belongsTo(Upazila::class); }
}
