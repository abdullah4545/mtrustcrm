<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'quotation_no','branch_id','lead_id','organization_id','organization_contact_id',
        'client_name','client_phone','client_email','client_address',
        'issue_date','valid_until','currency','calculate_tax','tax_enabled','tax_rate','description',
        'note_for_recipient','terms','require_signature',
        'sub_total','discount_amount','tax_amount','grand_total',
        'status_stage_id','prepared_by'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'valid_until' => 'date',
        'require_signature' => 'boolean',
        'tax_enabled' => 'boolean',
        'tax_rate' => 'decimal:2',
        'sub_total' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function items(){ return $this->hasMany(QuotationItem::class); }
    public function lead(){ return $this->belongsTo(Lead::class); }
    public function statusStage(){ return $this->belongsTo(StatusStage::class,'status_stage_id'); }
    public function organization(){ return $this->belongsTo(Organization::class); }
    public function organizationContact(){ return $this->belongsTo(OrganizationContact::class,'organization_contact_id'); }
    public function branch(){ return $this->belongsTo(Branch::class); }
    public function preparedBy(){ return $this->belongsTo(User::class,'prepared_by'); }
}