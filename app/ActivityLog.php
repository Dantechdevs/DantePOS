<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    /**
     * Cast properties to array
     */
    public function getPropertiesAttribute($value)
    {
        return json_decode($value, true) ?: [];
    }

    /**
     * Set properties as JSON
     */
    public function setPropertiesAttribute($value)
    {
        $this->attributes['properties'] = json_encode($value);
    }

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Polymorphic relationship
     */
    public function model()
    {
        return $this->morphTo();
    }

    /**
     * Scopes
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('description', 'like', "%{$search}%");
    }
}
