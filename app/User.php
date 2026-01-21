<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;           // Must be imported
use App\Traits\LogsActivity;                // Your custom trait

class User extends Authenticatable
{
    use Notifiable;
    use HasApiTokens;       // MUST be on its own line
    use LogsActivity;       // Your custom trait - keep it

    protected $fillable = [
        'group_id', 'user_type', 'username', 'name', 'email', 'password',
        'code', 'mobile', 'address', 'gender', 'image', 'status', 'createdBy'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relationships
    public function group()
    {
        return $this->belongsTo('App\Group', 'group_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'createdBy');
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($user) {
            if (empty($user->username)) {
                $user->username = static::generateUniqueUsername($user->name);
            }
        });

        static::deleting(function ($user) {
            if ($user->user_type == 'superadmin') {
                abort(422, "Super Admin cannot be deleted.");
            }
        });
    }

    protected static function generateUniqueUsername($fname)
    {
        $baseUsername = Str::slug((string)$fname);
        $username = $baseUsername;
        $counter = 1;

        while (static::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }
}
