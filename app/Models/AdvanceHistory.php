<?php

namespace App\Models;

use App\Traits\HasDateWithTime;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class AdvanceHistory extends Model
{

    use HasDateWithTime, LogsActivity;
    public function employees(){
		return $this->belongsTo('App\Models\Employee','employee_id');
	}

	public function users(){
		return $this->belongsTo('App\User','createdBy');
	}
	public function gate(){
		return $this->belongsTo('App\Models\Gate','gate_id');
	}
}
