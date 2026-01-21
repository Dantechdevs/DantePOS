<?php

namespace App\Models;

use App\Models\Customer;
use App\SchemeTransaction;
use App\Services\LuckyDrawService;
use App\SiteSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerScheme extends Model
{
    protected $fillable = [
        'customer_id',
        'total_sales',
        'redeemed_amount',
        'available_amount',
        'current_cycle_amount',
        'cycles_completed',
        'bonus_amount',
        'is_active'
    ];

    protected $casts = [
        'total_sales' => 'decimal:2',
        'redeemed_amount' => 'decimal:2',
        'available_amount' => 'decimal:2',
        'current_cycle_amount' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SchemeTransaction::class);
    }

    public function accumulateSchemeAmount(float $amount, ?int $orderId = null): array
    {
        $this->current_cycle_amount += $amount;
        $this->total_sales += $amount;
        $this->save();

        $bonusAwarded = false;
        $bonusAmount = 0;
        $settings = SiteSetting::pluck('value', 'key');

        // Check if threshold reached for bonus
        if ($this->current_cycle_amount >= $settings['threshold_amount']) {
            $bonusAmount = $this->awardBonus($orderId);
            $bonusAwarded = true;

            // Add to lucky draw
            $luckyDrawService = app(LuckyDrawService::class);
            $luckyDrawService->addEntryOnCycleCompletion(
                $this,
                $this->cycles_completed
            );
        }

        $this->transactions()->create([
            'sale_id' => $orderId,
            'type' => 'accumulation',
            'amount' => $amount,
            'description' => "Scheme product purchase"
        ]);

        return [
            'accumulated' => $amount,
            'bonus_awarded' => $bonusAwarded,
            'bonus_amount' => $bonusAmount,
            'current_cycle' => $this->current_cycle_amount,
            'cycles_completed' => $this->cycles_completed
        ];
    }

    protected function awardBonus(?int $orderId = null): float
    {
        $bonusAmount = $this->bonus_amount;

        $this->available_amount += $bonusAmount;
        $this->cycles_completed += 1;
        $this->current_cycle_amount = 0; // Reset for next cycle
        $this->save();

        $this->transactions()->create([
            'sale_id' => $orderId,
            'type' => 'bonus',
            'amount' => $bonusAmount,
            'description' => "Bonus awarded for reaching threshold"
        ]);

        return $bonusAmount;
    }

    public function redeemAmount(float $amount, ?int $orderId = null): bool
    {
        if ($amount > $this->available_amount) {
            return false;
        }

        $this->redeemed_amount += $amount;
        $this->available_amount -= $amount;
        $this->save();

        $this->transactions()->create([
            'sale_id' => $orderId,
            'type' => 'redemption',
            'amount' => $amount,
            'description' => "Amount redeemed for purchase"
        ]);

        return true;
    }

    public function getProgressPercentage(): float
    {
        $threshold = $settings['threshold_amount'] ?? 50000;
        // dd(min(100, ($this->current_cycle_amount / $threshold) * 100));
        return min(100, ($this->current_cycle_amount / $threshold) * 100);
    }
}
