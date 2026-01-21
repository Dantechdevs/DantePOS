<?php

namespace App;

use App\Models\Sale;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Godown extends Model
{
    use LogsActivity;
    public function user()
    {
        return $this->belongsTo('App\User', 'createdBy');
    }
    public function sale()
    {
        return $this->hasMany(Sale::class, 'godown_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($godown) {

            if ($godown->sale()->count() > 0) {
                if (request()->ajax()) {
                    abort(422, "Godwon have associated sales.");
                } else {
                    throw new \Exception("Godwon have associated sales.");
                }
            }
        });
    }
}
