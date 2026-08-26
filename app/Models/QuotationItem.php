<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id','product_id','item_name','description','qty','unit',
        'unit_price','tax_rate','tax_amount','line_total'
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function quotation(){ return $this->belongsTo(Quotation::class); }
    public function product(){ return $this->belongsTo(Product::class); }
}