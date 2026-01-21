<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class EmployeeAdvance extends Model
{
    use LogsActivity;
    public function employees(){
		return $this->belongsTo('App\Models\Employee','employee_id');
	}
	public function users(){
		return $this->belongsTo('App\User','createdBy');
	}
}
