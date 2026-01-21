<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerDiscount extends Model
{
    protected $fillable = [
        'customer_id', 'discount_amount', 'createdBy'
    ];
}
