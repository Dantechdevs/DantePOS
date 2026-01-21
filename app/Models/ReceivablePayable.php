<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceivablePayable extends Model
{
    public function users(){
        return $this->belongsTo('App\User','createdBy');
    }
    public function customers(){
        return $this->belongsTo('App\Models\Customer','customer_id');
    }
}
