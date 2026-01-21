<?php

namespace App\Models;

use App\CustomerPayment;
use App\Traits\HasDateWithTime;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Sale extends Model
{
    use HasDateWithTime, LogsActivity;
    protected $fillable = [
        'local_id',
        'invoice_no',
        'date',
        'sale_type',
        'customer_id',
        'godown_id',
        'area_id',
        'description',
        'sub_total',
        'other_charges',
        'discount_type',
        'discount',
        'discount_amount',
        'courier',
        'payment_type',
        'payment_status',
        'grand_total',
        'paid_amount',
        'balance_amount',
        'change_amount',
        'due_date',
        'items_addon',
        'total_qty',
        'status',
        'createdBy',
    ];

    public function customerPayments()
    {
        return $this->hasMany(CustomerPayment::class, 'sale_id');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Customer', 'customer_id');
    }
    public function areas()
    {
        return $this->belongsTo('App\Models\Area', 'area_id');
    }
    public function users()
    {
        return $this->belongsTo('App\User', 'createdBy');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($sale) {
            // $lastInvoice = self::orderBy('id', 'desc')->first();
            // $dateTime = date('dmY');
            // if ($lastInvoice && preg_match('/\d+-(\d+)$/', $lastInvoice->invoice_no, $matches)) {
            //     $lastNumber = (int)$matches[1] + 1;
            // } else {
            //     $lastNumber = 1;
            // }

            // // $sale->invoice_no = $dateTime . '-' . $lastNumber;
            // $sale->invoice_no =  $lastNumber;

            $lastInvoice = self::orderBy('id', 'DESC')->first();
            $nextInvoiceNo = $lastInvoice ? ($lastInvoice->invoice_no + 1) : 1;
            $sale->invoice_no =   $nextInvoiceNo;

        });

        static::deleting(function ($sale) {
            if (Auth::check() && Auth::user()->user_type !== 'superadmin') {
                if (request()->ajax()) {
                    // Abort with a 403 status code for unauthorized AJAX requests
                    abort(403, "Only superadmins can delete sales.");
                } else {
                    // Throw an exception for non-AJAX requests
                    throw new \Exception("Only superadmins can delete sales.");
                }
            }

            // Prevent deletion if the status is 'confirmed' or 'pending'
            if (in_array($sale->status, [1, 2])) {
                if (request()->ajax()) {
                    // Abort with a 422 status code and a clear error message
                    abort(422, "You cannot delete a sale with a status of 'confirmed' or 'pending'.");
                } else {
                    // Throw an exception for non-AJAX requests
                    throw new \Exception("You cannot delete a sale with a status of 'confirmed' or 'pending'.");
                }
            }
        });
    }
}
