<?php

namespace App;

use App\Models\CustomerScheme;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchemeTransaction extends Model
{
    protected $fillable = [
        'customer_scheme_id',
        'sale_id',
        'type',
        'amount',
        'description'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public function customerScheme(): BelongsTo
    {
        return $this->belongsTo(CustomerScheme::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
