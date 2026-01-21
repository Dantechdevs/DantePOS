<?php

namespace App\Models;

use App\Traits\HasDateWithTime;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class CustomerOpeningBalance extends Model
{
    use LogsActivity;
    // use HasDateWithTime;
    protected $fillable = [
        'invoice_no',
        'date',
        'customer_id',
        'type',
        'description',
        'amount',
        'createdBy'
    ];
    public function users(){
        return $this->belongsTo('App\User','createdBy','id');
    }


    public function customers()
    {
        return $this->belongsTo('App\Models\Customer','customer_id','id');
    }
}
