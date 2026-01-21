<?php

namespace App\Models;

use App\Traits\HasDateWithTime;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class SupplierPayment extends Model
{
    use HasDateWithTime, LogsActivity;
    protected $fillable = [
        'purchase_no',
        'date',
        'supplier_id',
        'description',
        'amount',
        'createdBy'
    ];
     public function users(){
        return $this->belongsTo('App\User','createdBy');
    }
    public function supplier(){
        return $this->belongsTo('App\Models\Supplier','supplier_id');
    }
}
