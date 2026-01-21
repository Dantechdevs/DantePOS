<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerScheme;
use App\Models\Product;
use App\Models\Sale;
use App\SiteSetting;
use Illuminate\Support\Facades\DB;

class SchemeService
{
    public function processOrder(Sale $sale): array
    {
        $result = [
            'scheme_amount' => 0,
            'bonus_awarded' => false,
            'bonus_amount' => 0,
            'current_cycle_progress' => 0
        ];

        // Only process if sale is confirmed and has a customer
        if ($sale->status != 1 || !$sale->customer_id) {
            return $result;
        }

        DB::transaction(function () use ($sale, &$result) {
            $customer = $sale->customer;

            $scheme = $this->getOrCreateCustomerScheme($customer);

            // Calculate scheme products amount only
            $schemeAmount = $this->calculateSchemeProductsAmount($sale);

            if ($schemeAmount > 0) {
                $accumulationResult = $scheme->accumulateSchemeAmount($schemeAmount, $sale->id);

                $result = array_merge($result, $accumulationResult);

            }

            $result['current_cycle_progress'] = $scheme->getProgressPercentage();
            // echo "<pre>"; print_r($result['current_cycle_progress']); echo "</pre>"; exit;
        });

        return $result;
    }

    protected function calculateSchemeProductsAmount(Sale $sale): float
    {
        $schemeAmount = 0;

        // Check if items_addon is serialized and decode it
        $items = $sale->items_addon;
        if (is_string($items)) {
            $items = unserialize($items);
        }


        if (is_array($items)) {
            foreach ($items as $item) {

                // Handle both object and array formats
                $productId = is_object($item) ? $item->product_id : ($item['product_id'] ?? null);

                $quantity = is_object($item) ? $item->quantity : ($item['quantity'] ?? 0);
                $unitPrice = is_object($item) ? $item->selling_price : ($item['selling_price'] ?? 0);

                if ($productId && Product::isSchemeProduct($productId)) {
                    // echo '<pre>'; print_r(Product::isSchemeProduct($productId)); echo '</pre>'; exit;
                    $schemeAmount += $quantity * $unitPrice;
                }
            }
        }

        return $schemeAmount;
    }

    public function redeemForOrder(Sale $sale, float $redeemAmount): bool
    {
        // Only allow redemption for confirmed sales
        if ($sale->status != 1 || !$sale->customer_id) {
            return false;
        }

        return DB::transaction(function () use ($sale, $redeemAmount) {
            $customer = $sale->customer;
            $scheme = $this->getCustomerScheme($customer);

            if (!$scheme || !$this->canRedeemAmount($scheme, $redeemAmount)) {
                return false;
            }

            return $scheme->redeemAmount($redeemAmount, $sale->id);
        });
    }

    public function getOrCreateCustomerScheme(Customer $customer): CustomerScheme
    {
        return CustomerScheme::firstOrCreate(
            ['customer_id' => $customer->id],
            [
                'total_sales' => 0,
                'redeemed_amount' => 0,
                'available_amount' => 0,
                'current_cycle_amount' => 0,
                'cycles_completed' => 0,
                'bonus_amount' => config('scheme.bonus_amount', 5000),
                'is_active' => true
            ]
        );
    }

    public function getCustomerScheme(Customer $customer): ?CustomerScheme
    {
        return CustomerScheme::where('customer_id', $customer->id)->first();
    }

    public function canRedeemAmount(CustomerScheme $scheme, float $amount): bool
    {
        return $scheme->available_amount >= $amount && $amount > 0;
    }

    public function getSchemeProgress(Customer $customer): array
    {
        $scheme = $this->getCustomerScheme($customer);
        $settings = SiteSetting::pluck('value', 'key');
        if (!$scheme) {
            return [
                'current_cycle_amount' => 0,
                // 'threshold' => config('scheme.redemption_threshold', 50000),
                'threshold' => $settings['threshold_amount'] ?? 50000,
                'progress_percentage' => 0,
                'available_bonus' => 0,
                'cycles_completed' => 0,
                'next_bonus' => config('scheme.bonus_amount', 5000)
            ];
        }

        return [
            'current_cycle_amount' => $scheme->current_cycle_amount,
            'threshold' => $settings['threshold_amount'] ?? 50000,
            'progress_percentage' => $scheme->getProgressPercentage(),
            'available_bonus' => $scheme->available_amount,
            'cycles_completed' => $scheme->cycles_completed,
            'next_bonus' => $scheme->bonus_amount
        ];
    }

    /**
     * Get available redemption amount for customer
     */
    public function getAvailableRedemptionAmount(Customer $customer): float
    {
        $scheme = $this->getCustomerScheme($customer);
        return $scheme ? $scheme->available_amount : 0;
    }
}
