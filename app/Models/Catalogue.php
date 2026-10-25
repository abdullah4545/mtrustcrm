<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Catalogue extends Model
{
    protected $fillable = ['title','category_id','description','file_path','status','created_by'];

    public function category() { return $this->belongsTo(ProductCategory::class, 'category_id'); }
}
