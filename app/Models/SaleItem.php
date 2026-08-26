<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id','product_id','item_name','description','qty','unit',
        'unit_price','tax_rate','tax_amount','line_total','purchase_price_snapshot'
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
        'purchase_price_snapshot' => 'decimal:2',
    ];

    public function sale(){ return $this->belongsTo(Sale::class); }
    public function product(){ return $this->belongsTo(Product::class); }
}