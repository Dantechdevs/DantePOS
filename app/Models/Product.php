<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use LogsActivity;
    protected $fillable = [
        'id',
        'unit_id',
        'supplier_id',
        'product_code',
        'name',
        'name_ur',
        'stock_alert',
        'expiry_date',
        'unit_info',
        'quantity',
        'is_scheme_product',
        'status',
        'createdBy'
    ];

    protected $casts = [
        'unit_info' => 'array'
    ];

    public function getDisplayStock($quantity = null)
    {
        $baseQuantity = $quantity ?? $this->quantity;
        $unitInfo = $this->unit_info ?? [];

        if (empty($unitInfo)) {
            return $baseQuantity . ' units';
        }

        // Get the smallest unit (last unit added)
        $baseUnit = end($unitInfo);
        $baseUnitConversion = $baseUnit['conversion'] ?? 1;

        // Convert quantity to smallest unit
        $quantityInSmallestUnit = $baseQuantity * $baseUnitConversion;

        // Sort all units by conversion factor (descending)
        usort($unitInfo, function ($a, $b) {
            return $b['conversion'] <=> $a['conversion'];
        });

        $remaining = $quantityInSmallestUnit;
        $output = [];

        foreach ($unitInfo as $unit) {
            if ($unit['conversion'] <= 0) continue;

            $count = floor($remaining / $unit['conversion']);
            if ($count > 0) {
                $output[] = $count . ' ' . $unit['unit'];
                $remaining = $remaining % $unit['conversion'];
            }
        }

        // Add remaining in smallest unit if any left
        if ($remaining > 0 || empty($output)) {
            $smallestUnit = end($unitInfo);
            $output[] = $remaining . ' ' . $smallestUnit['unit'];
        }

        return implode(', ', $output);
    }

    public function users()
    {
        return $this->belongsTo('App\User', 'createdBy');
    }
    public function units()
    {
        return $this->belongsTo('App\Models\Unit', 'unit_id');
    }
    public function supplier()
    {
        return $this->belongsTo('App\Models\Supplier', 'supplier_id');
    }

    public static function isSchemeProduct(int $productId): bool
    {
        return static::where('id', $productId)
            ->where('is_scheme_product', true)
            ->exists();
    }



    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($product) {

            if (Auth::check() && Auth::user()->user_type !== 'superadmin') {
                if (request()->ajax()) {
                    // Abort with a 403 status code for unauthorized AJAX requests
                    abort(403, "Only superadmins can delete product.");
                } else {
                    // Throw an exception for non-AJAX requests
                    throw new \Exception("Only superadmins can delete product.");
                }
            }

            $productId = $product->id;

            // Check in `sales` table
            $existsInSales = DB::table('sales')
                ->whereRaw('items_addon LIKE ?', ['%"product_id";s:' . strlen($productId) . ':"' . $productId . '"%'])
                ->exists();

            // Check in `purchases` table
            $existsInPurchases = DB::table('purchases')
                ->whereRaw('items_addon LIKE ?', ['%"product_id";s:' . strlen($productId) . ':"' . $productId . '"%'])
                ->exists();

            if ($existsInSales || $existsInPurchases) {
                if (request()->ajax()) {
                    abort(422, "This product cannot be deleted as it exists in sales or purchases records.");
                } else {
                    throw new \Exception("This product cannot be deleted as it exists in sales or purchases records.");
                }
                // throw new \Exception('This product cannot be deleted as it exists in sales or purchases records.');
            }
        });
    }
}
