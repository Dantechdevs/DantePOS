<?php

namespace App;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use LogsActivity;
    protected $fillable = ['key', 'value', 'createdBy'];

    public function user()
    {
        return $this->belongsTo(User::class, 'createdBy', 'id');
    }
}
