<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLeave extends Model
{
    public function employee(){ 
		return $this->belongsTo('App\Models\Employee','employee_id');
	}
	public function LeavePurpose(){ 
		return $this->belongsTo('App\Models\LeavePurpose','employee_leave_id');
	}
}
