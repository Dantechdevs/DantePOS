<?php

namespace App\Models;

use App\Traits\LogsActivity;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use LogsActivity;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'salary',
        'joining_date',
        'createdBy',
        // Add other fillable fields as needed
    ];

    /**
     * Get the user who created this employee
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'createdBy', 'id');
    }

    /**
     * Get all advance payments for this employee
     */
    public function advanceHistories(): HasMany
    {
        return $this->hasMany(AdvanceHistory::class, 'employee_id');
    }

    /**
     * Get all advance returns for this employee
     */
    public function returnAdvances(): HasMany
    {
        return $this->hasMany(EmployeeReturnAdvance::class, 'employee_id');
    }

    /**
     * Get all monthly salary records for this employee
     */
    public function monthlySalaries(): HasMany
    {
        return $this->hasMany(MonthlySalary::class, 'employee_id');
    }

    /**
     * Calculate total advance amount for this employee
     */
    public function getTotalAdvanceAttribute(): float
    {
        return $this->advanceHistories->sum('current_paidAmount');
    }

    /**
     * Calculate total returned amount for this employee
     */
    public function getTotalReturnedAttribute(): float
    {
        return $this->returnAdvances->sum('return_amount');
    }

    /**
     * Calculate remaining advance balance for this employee
     */
    public function getRemainingAdvanceAttribute(): float
    {
        return $this->total_advance - $this->total_returned;
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($employee) {
            if ($employee->advanceHistories()->exists() ||
                $employee->returnAdvances()->exists() ||
                $employee->monthlySalaries()->exists()) {

                if (request()->ajax()) {
                    abort(422, "Cannot delete employee. There are associated records in advance histories, return advances, or monthly salaries.");
                }
                throw new \Exception("Cannot delete employee. There are associated records in advance histories, return advances, or monthly salaries.");
            }
        });
    }
}
