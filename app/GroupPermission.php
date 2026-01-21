<?php

namespace App;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class GroupPermission extends Model
{
    use LogsActivity;
    protected $fillable = ['group_id', 'module_id', 'module_name', 'module_page', 'access'];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
