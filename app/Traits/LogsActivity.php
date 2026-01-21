<?php

namespace App\Traits;

use App\Services\ActivityLogService;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            ActivityLogService::create($model);
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            // Remove timestamps from changes
            unset($changes['updated_at']);

            if (!empty($changes)) {
                ActivityLogService::update($model, [
                    'old' => array_intersect_key($model->getOriginal(), $changes),
                    'new' => $changes
                ]);
            }
        });

        static::deleted(function ($model) {
            ActivityLogService::delete($model);
        });
    }

    public function getActivityLogDescription()
    {
        if (isset($this->name)) {
            return $this->name;
        }

        if (isset($this->title)) {
            return $this->title;
        }

        if (isset($this->email)) {
            return $this->email;
        }

        return "ID: {$this->getKey()}";
    }
}
