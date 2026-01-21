<?php

namespace App\Models;

use App\Traits\LogsActivity;
use App\User;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use LogsActivity;
    public function expenses()
    {
        return $this->hasMany(Expense::class,'exp_category_id','id');
    }
    public function user(){
        return $this->belongsTo(User::class,'createdBy','id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($category) {

            if ($category->expenses()->count() > 0) {
                if (request()->ajax()) {
                    abort(422, "Expense Category has associated Data.");
                } else {
                    throw new \Exception("Expense Category has associated Data.");
                }
            }
        });
    }
}
