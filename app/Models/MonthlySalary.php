<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class MonthlySalary extends Model
{
    use LogsActivity;
    protected $fillable = [
        'employee_id',
        'date',
        'amount',
        'createdBy',
    ];
    public function employee(){
		return $this->belongsTo('App\Models\Employee','employee_id');
	}
}
