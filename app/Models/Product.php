<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id','subcategory_id','brand_id','sku','name','description',
        'sale_price','purchase_price','vat_rate','tax_rate',
        'warranty_months','warranty_terms_details','configuration_description',
        'image_url','status'
    ];

    public function category(){ return $this->belongsTo(ProductCategory::class,'category_id'); }
    public function subcategory(){ return $this->belongsTo(ProductSubcategory::class,'subcategory_id'); }
    public function brand(){ return $this->belongsTo(Brand::class,'brand_id'); }
}
