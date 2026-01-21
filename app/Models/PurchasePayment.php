<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchasePayment extends Model
{
    public function suppliers(){ 
		return $this->belongsTo('App\Models\Supplier','supplier_id');
	}
	public function purchase(){ 
		return $this->belongsTo('App\Models\Purchase','purchase_id');
	}
}
