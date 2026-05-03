<?php

namespace App\Services;

use App\Models\Trace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ActivityLogger
{
    public static function log(string $action, array $payload = [], ?int $userId = null): void
    {
        $request = request();
        $userId ??= Auth::id();

        Trace::create([
            'user_id' => $userId,
            'action' => $action,
            'payload' => $payload ?: null,
            'ip' => $request?->ip(),
            'user_agent' => substr((string) $request?->userAgent(), 0, 250),
            'created_at' => now(),
        ]);

        Log::channel('trace')->info($action, [
            'user_id' => $userId,
            'ip' => $request?->ip(),
            'payload' => $payload,
        ]);
    }
}
