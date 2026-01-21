<?php

namespace App;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use LogsActivity;
    protected $fillable = ['name','createdBy'];

    public function permissions()
    {
        return $this->hasMany(GroupPermission::class,'group_id','id');
    }
    public function single_user()
    {
        return $this->belongsTo(User::class,'createdBy','id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($group) {
            // Check if the group is assigned to any user
            if ($group->users()->exists()) {
                if (request()->ajax()) {
                    abort(422, "Group cannot be deleted as it is assigned to one or more users.");
                } else {
                    throw new \Exception("Group cannot be deleted as it is assigned to one or more users.");
                }
                // throw new \Exception('Group cannot be deleted as it is assigned to one or more users.');
            }
        });
    }
}
