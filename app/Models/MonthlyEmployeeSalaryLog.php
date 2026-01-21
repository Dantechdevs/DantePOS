<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class MonthlyEmployeeSalaryLog extends Model
{
    use LogsActivity;
    public function gates(){
		return $this->belongsTo('App\Models\Gate','gate_id');
	}
}
