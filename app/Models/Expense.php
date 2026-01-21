<?php

namespace App\Models;

use App\Traits\HasDateWithTime;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasDateWithTime , LogsActivity;
    protected $fillable = [
        'date',
        'exp_category_id',
        'expense_for',
        'amount',
        'note',
        'createdBy'
    ];

    public function user()
    {
        return $this->belongsTo('App\User', 'createdBy');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\ExpenseCategory', 'exp_category_id');
    }

    // protected static function boot()
    // {
    //     parent::boot();
    //     // Include the trait's functionality
    //     static::bootHasDateWithTime();

    //     // Use the `saving` event for both creating and updating
    //     static::saving(function ($expense) {});
    // }
}
