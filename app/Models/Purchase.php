<?php

namespace App\Models;

use App\Traits\HasDateWithTime;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasDateWithTime,  LogsActivity;
    protected $fillable = [
        'purchase_no',
        'date',
        'supplier_id',
        'description',
        'sub_total',
        'other_charges',
        'discount_type',
        'discount',
        'discount_amount',
        'grand_total',
        'items_addon',
        'total_qty',
        'status',
        'attachment',
        'createdBy',
    ];

    public function supplier()
    {
        return $this->belongsTo('App\Models\Supplier', 'supplier_id');
    }
    public function users()
    {
        return $this->belongsTo('App\User', 'createdBy');
    }

    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($purchase) {
            // Prevent deletion if the status is 'received' or 'pending'
            if (in_array($purchase->status, ['received', 'pending'])) {
                if (request()->ajax()) {
                    // Abort with a 422 status code and a clear error message
                    abort(422, "You cannot delete a purchase with a status of 'received' or 'pending'.");
                } else {
                    // Throw an exception for non-AJAX requests
                    throw new \Exception("You cannot delete a purchase with a status of 'received' or 'pending'.");
                }
            }
        });
    }
}
