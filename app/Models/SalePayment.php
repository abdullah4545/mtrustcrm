<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    protected $fillable = [
        'sale_id','branch_id','payment_date','amount','method','transaction_ref','received_by','note'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function sale(){ return $this->belongsTo(Sale::class); }
}