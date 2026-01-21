<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierOpeningBalance extends Model
{
    public function users(){
        return $this->belongsTo('App\User','createdBy');
    }
    public function suppliers(){
        return $this->belongsTo('App\Models\Supplier','supplier_id');
    }
}
