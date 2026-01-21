<?php

namespace App\Models;

use App\ProductUnit;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Unit extends Model
{
    use LogsActivity;
    protected $fillable = ['name', 'createdBy'];

    public function user()
    {
        return $this->belongsTo('App\User', 'createdBy');
    }
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($unit) {

            if (Auth::check() && Auth::user()->user_type !== 'superadmin') {
                if (request()->ajax()) {
                    // Abort with a 403 status code for unauthorized AJAX requests
                    abort(403, "Only superadmins can delete unit.");
                } else {
                    // Throw an exception for non-AJAX requests
                    throw new \Exception("Only superadmins can delete unit.");
                }
            }

            $unitId = $unit->id;

            // Check in `sales` table
            $existsInProducts = DB::table('products')
                ->whereRaw('unit_info LIKE ?', ['%"unit_id":"' . $unitId . '"%'])
                ->orWhereRaw('unit_info LIKE ?', ['%"unit_id": "' . $unitId . '"%'])
                ->exists();

            // Check in `purchases` table
            $existsInPurchases = DB::table('purchases')
                ->where(function ($query) use ($unitId) {
                    $query->whereRaw('items_addon LIKE ?', ['%"unit_id";i:' . $unitId . ';%']) // integer type
                        ->orWhereRaw('items_addon LIKE ?', ['%"unit_id";s:' . strlen($unitId) . ':"' . $unitId . '"%']); // string type
                })
                ->exists();

            $existsInSales = DB::table('sales')
                ->where(function ($query) use ($unitId) {
                    $query->whereRaw('items_addon LIKE ?', ['%"unit_id";i:' . $unitId . ';%']) // integer type
                        ->orWhereRaw('items_addon LIKE ?', ['%"unit_id";s:' . strlen($unitId) . ':"' . $unitId . '"%']); // string type
                })
                ->exists();


            if ($existsInProducts || $existsInSales || $existsInPurchases) {
                if (request()->ajax()) {
                    abort(422, "This unit cannot be deleted as it exists in products, sales or purchases records.");
                } else {
                    throw new \Exception("This unit cannot be deleted as it exists in products, sales or purchases records.");
                }
                // throw new \Exception('This unit cannot be deleted as it exists in sales or purchases records.');
            }
        });
    }
}
