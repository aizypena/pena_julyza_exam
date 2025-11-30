<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    /**
     * Log an activity.
     */
    public static function log(
        string $action,
        string $description,
        ?string $modelType = null,
        ?int $modelId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): ActivityLog {
        $request = request();
        
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * Log a login action.
     */
    public static function logLogin(int $userId): ActivityLog
    {
        return self::log('login', 'User logged in', 'User', $userId);
    }

    /**
     * Log a logout action.
     */
    public static function logLogout(int $userId): ActivityLog
    {
        return self::log('logout', 'User logged out', 'User', $userId);
    }

    /**
     * Log a create action.
     */
    public static function logCreate(string $modelType, int $modelId, string $name, array $newValues): ActivityLog
    {
        return self::log(
            'create',
            "Created {$modelType}: {$name}",
            $modelType,
            $modelId,
            null,
            $newValues
        );
    }

    /**
     * Log an update action.
     */
    public static function logUpdate(string $modelType, int $modelId, string $name, array $oldValues, array $newValues): ActivityLog
    {
        return self::log(
            'update',
            "Updated {$modelType}: {$name}",
            $modelType,
            $modelId,
            $oldValues,
            $newValues
        );
    }

    /**
     * Log a delete action.
     */
    public static function logDelete(string $modelType, int $modelId, string $name, array $oldValues): ActivityLog
    {
        return self::log(
            'delete',
            "Deleted {$modelType}: {$name}",
            $modelType,
            $modelId,
            $oldValues,
            null
        );
    }
}
