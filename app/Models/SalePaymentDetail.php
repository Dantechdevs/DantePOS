<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalePaymentDetail extends Model
{
    public function users(){ 
        return $this->belongsTo('App\User','updated_by');
    }
}
