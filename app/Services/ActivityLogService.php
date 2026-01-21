<?php

namespace App\Services;

use App\ActivityLog;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    public static function log($action, $description, $model = null, $properties = null, $user = null)
    {
        $user = $user ?? Auth::user();
        $request = request();

        $logData = [
            'user_id' => $user ? $user->id : null,
            'action' => $action,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->header('User-Agent') : null,
        ];

        if ($model && $model instanceof Model) {
            $logData['model_type'] = get_class($model);
            $logData['model_id'] = $model->id;
        }

        return ActivityLog::create($logData);
    }

    public static function login(User $user)
    {
        return self::log('login', "User {$user->email} logged in", $user);
    }

    public static function logout(User $user)
    {
        return self::log('logout', "User {$user->email} logged out", $user);
    }

    public static function create(Model $model, $data = [])
    {
        $modelName = class_basename($model);
        return self::log(
            'create',
            "Created new {$modelName}: " . self::getModelIdentifier($model),
            $model,
            $data
        );
    }

    public static function update(Model $model, $changes = [])
    {
        $modelName = class_basename($model);
        return self::log(
            'update',
            "Updated {$modelName}: " . self::getModelIdentifier($model),
            $model,
            ['changes' => $changes]
        );
    }

    public static function delete(Model $model)
    {
        $modelName = class_basename($model);
        return self::log(
            'delete',
            "Deleted {$modelName}: " . self::getModelIdentifier($model),
            $model
        );
    }

    protected static function getModelIdentifier(Model $model)
    {
        if (method_exists($model, 'getActivityLogDescription')) {
            return $model->getActivityLogDescription();
        }

        if ($model->getKey()) {
            return "ID: {$model->getKey()}";
        }

        return 'New record';
    }
}
