<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ActivityBillHistory extends Model
{
    protected $table='activity_bill_histories';
    public $timestamps=false;
    protected $guarded=['*'];
    protected $casts=['created_at'=>'datetime'];
}
