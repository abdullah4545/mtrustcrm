<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    protected $fillable = ['title', 'status'];

    protected $casts = [
        'status' => 'boolean',
    ];
}