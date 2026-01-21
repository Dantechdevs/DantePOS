<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use LogsActivity;
    protected $fillable = [
        'id',
        'name',
        'email',
        'mobile',
        'national_id',
        'address',
        'opening_balance',
        'available_days',
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

    // Define relationship with Payment
    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function purchase()
    {
        return $this->hasMany(Purchase::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($supplier) {

            if ($supplier->purchase()->count() > 0 || $supplier->payments()->count() > 0) {
                if (request()->ajax()) {
                    abort(422, "Supplier have associated purchases or payments.");
                } else {
                    throw new \Exception("Supplier have associated purchases or payments.");
                }
            }
        });
    }
}
