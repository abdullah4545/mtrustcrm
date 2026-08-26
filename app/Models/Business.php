<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $table = 'businesses';

    protected $fillable = [
        'business_name','business_email','business_phone','business_address',
        'timezone','currency','currency_symbol','logo','fav_icon','vat',
        'title','meta_title','meta_description','meta_keywords','meta_image',
        'facebook','instagram','twitter','linkedin','youtube','whatsapp','tiktok','pinterest',
        'chatbot','head_code','body_code',
    ];
}
