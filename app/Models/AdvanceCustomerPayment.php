<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvanceCustomerPayment extends Model
{
   public function users(){ 
        return $this->belongsTo('App\User','user_id');
    }
    public function customers(){ 
        return $this->belongsTo('App\Models\Customer','customer_id');
    }
}
