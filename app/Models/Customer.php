<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use LogsActivity;
    protected $fillable = [
        'id',
        'area_id',
        'name',
        'name_ur',
        'email',
        'mobile',
        'national_id',
        'address',
        'opening_balance',
        'createdBy',
    ];

    public function user()
    {
        return $this->belongsTo('App\User', 'createdBy');
    }
    public function area()
    {
        return $this->belongsTo(Area::class);
    }
    public function attachments()
    {
        return $this->hasMany(CustomerPaymentReceipt::class, 'customer_id');
    }

    // Define relationship with Payment
    public function payments()
    {
        return $this->hasMany(CustomerOpeningBalance::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function customer()
    {
        return $this->hasOneThrough(
            Customer::class,
            Sale::class,
            'id', // Foreign key on Sale table
            'id', // Foreign key on Customer table
            'sale_id', // Local key on CustomerPayment table
            'customer_id' // Local key on Sale table
        );
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($customer) {

            if ($customer->sales()->count() > 0 || $customer->payments()->count() > 0) {
                if (request()->ajax()) {
                    abort(422, "Customer have associated sales or payments.");
                } else {
                    throw new \Exception("Customer have associated sales or payments.");
                }
            }
        });
    }
}
