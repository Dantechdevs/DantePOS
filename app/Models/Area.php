<?php

namespace App\Models;

use App\Traits\LogsActivity;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use LogsActivity;
    protected $fillable = ['name']; // Define fillable fields

    public function user()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    // Define relationship with Customer
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    // Get all payments through customers
    public function payments()
    {
        return $this->hasManyThrough(CustomerOpeningBalance::class, Customer::class);
    }

    public function areawisesale()
    {
        return $this->hasManyThrough(Sale::class, Customer::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($area) {

            if ($area->customers()->count() > 0) {
                if (request()->ajax()) {
                    abort(422, "Area have associated customers.");
                } else {
                    throw new \Exception("Area have associated customers.");
                }
            }
        });
    }
}
