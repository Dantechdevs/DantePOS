<?php

namespace App;

use App\Models\Customer;
use App\Models\CustomerScheme;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LuckyDrawEntry extends Model
{
    protected $fillable = [
        'lucky_draw_id',
        'customer_id',
        'customer_scheme_id',
        'cycle_number',
        'entry_source',
        'is_winner',
        'prize_type',
        'prize_amount',
        'prize_won',
        'won_at',
        'is_winner'
    ];

    protected $casts = [
        'won_at' => 'datetime',
         'is_winner' => 'boolean'
    ];

    public function luckyDraw(): BelongsTo
    {
        return $this->belongsTo(LuckyDraw::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function customerScheme(): BelongsTo
    {
        return $this->belongsTo(CustomerScheme::class);
    }

    public function markAsWinner(string $prizeType, float $prizeAmount = null): void
    {
        $this->update([
            'is_winner' => true,
            'prize_type' => $prizeType,
            'prize_amount' => $prizeAmount,
            'won_at' => now()
        ]);
    }
}
