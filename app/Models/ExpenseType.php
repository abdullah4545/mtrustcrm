<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseType extends Model
{
    protected $table = 'expense_types';

    protected $fillable = ['name','status','sort_order','bill_required'];
    protected $casts = ['status' => 'boolean', 'bill_required' => 'boolean'];
}
