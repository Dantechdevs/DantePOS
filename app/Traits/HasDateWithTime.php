<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait HasDateWithTime
{
    public static function bootHasDateWithTime()
    {
        static::saving(function ($model) {
            // Log the raw input date for debugging
            Log::info('Raw Date Input: ' . $model->date);

            // Ensure the `date` field exists and is not empty
            if (!$model->date) {
                Log::error('Date field is empty or missing.');
                return;
            }

            if ($model->exists) {
                // Retrieve the original date (with time) and the submitted date
                $originalDateTime = $model->getOriginal('date'); // Original date with time
                $originalDate = date('Y-m-d', strtotime($originalDateTime)); // Original date only
                $submittedDate = date('Y-m-d', strtotime($model->date)); // Submitted date

                if ($submittedDate === $originalDate) {
                    // If the date part matches, retain the original date-time
                    $model->date = $originalDateTime;
                    Log::info('Date unchanged, keeping original date-time: ' . $originalDateTime);
                } else {
                    // If the date part differs, append the current time to the new date
                    $model->date = date('Y-m-d H:i:s', strtotime($model->date . ' ' . date('H:i:s')));
                    Log::info('Date changed, new value: ' . $model->date);
                }
            } else {
                // For new records, append the current time to the input date
                $model->date = date('Y-m-d H:i:s', strtotime($model->date . ' ' . date('H:i:s')));
                Log::info('New record, setting date to: ' . $model->date);
            }

            // Automatically set the `createdBy` field if it exists in the model
            if (in_array('createdBy', $model->getFillable())) {
                $model->createdBy = Auth::id();
            }
        });
    }
}
