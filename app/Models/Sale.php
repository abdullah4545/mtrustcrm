<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sale_no','invoice_no','branch_id','lead_id','quotation_id',
        'organization_id','organization_contact_id',
        'client_name','client_phone','client_email','client_address',
        'sold_by','sale_date','status_stage_id','tax_enabled','tax_rate',
        'sub_total','discount_amount','tax_amount','grand_total',
        'paid_total','due_total','payment_status',
        'notes'
    ];

    protected $casts = [
        'sale_date' => 'date',
        'tax_enabled' => 'boolean',
        'tax_rate' => 'decimal:2',
        'sub_total' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_total' => 'decimal:2',
        'due_total' => 'decimal:2',
    ];

    public function items(){ return $this->hasMany(SaleItem::class); }
    public function payments(){ return $this->hasMany(SalePayment::class); }

    public function statusStage(){ return $this->belongsTo(StatusStage::class,'status_stage_id'); }
    public function lead(){ return $this->belongsTo(Lead::class); }
    public function quotation(){ return $this->belongsTo(Quotation::class); }

    public function organization(){ return $this->belongsTo(Organization::class); }
    public function organizationContact(){ return $this->belongsTo(OrganizationContact::class,'organization_contact_id'); }
    public function branch(){ return $this->belongsTo(Branch::class); }
    public function soldBy(){ return $this->belongsTo(User::class,'sold_by'); }
}