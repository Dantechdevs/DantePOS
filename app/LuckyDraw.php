<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class LuckyDraw extends Model
{
    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'status',
        'max_entries_per_customer',
        'prizes',
        'draw_date'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'draw_date' => 'timestamp',
        'prizes' => 'array'
    ];

    /**
     * Scope a query to only include active lucky draws.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include inactive lucky draws.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope a query to only include currently active draws (within date range).
     */
    public function scopeCurrentlyActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
    }

    public function entries(): HasMany
    {
        return $this->hasMany(LuckyDrawEntry::class);
    }

    public function winners(): HasMany
    {
        return $this->entries()->where('is_winner', true);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' &&
            $this->start_date <= now() &&
            $this->end_date >= now();
    }

    public function canAddEntries(): bool
    {
        return $this->isActive() && $this->draw_date === null;
    }

    public function getTotalEntriesAttribute(): int
    {
        return $this->entries()->count();
    }

    public function getCustomerEntryCount($customerId): int
    {
        return $this->entries()->where('customer_id', $customerId)->count();
    }

    public function canCustomerEnter($customerId): bool
    {
        return $this->getCustomerEntryCount($customerId) < $this->max_entries_per_customer;
    }
}
