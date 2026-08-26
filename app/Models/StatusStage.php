<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusStage extends Model
{
    protected $fillable = [
        'is_for','name','color','status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}