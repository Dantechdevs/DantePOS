<?php

namespace App\Models;

use App\Traits\HasDateWithTime;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class EmployeeReturnAdvance extends Model
{
    use LogsActivity;
    use HasDateWithTime;
    public function employees(){
        return $this->belongsTo('App\Models\Employee','employee_id');
    }
}
