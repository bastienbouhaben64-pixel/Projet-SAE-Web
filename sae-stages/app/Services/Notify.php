<?php

namespace App\Services;

use App\Mail\NotificationMail;
use App\Models\Notification;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class Notify
{
    public static function send(Utilisateur|int $utilisateur, string $type, string $title, ?string $message = null, ?string $url = null): Notification
    {
        $user = $utilisateur instanceof Utilisateur
            ? $utilisateur
            : Utilisateur::find($utilisateur);

        $notif = Notification::create([
            'user_id' => $user?->id ?? (int) $utilisateur,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'url' => $url,
        ]);

        if ($user && config('notifications.email_enabled', true) && $user->email) {
            try {
                Mail::to($user->email)->send(new NotificationMail($title, $message, $url, $user->name));
            } catch (Throwable $e) {
                Log::warning('Notify email failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            }
        }

        return $notif;
    }

    public static function broadcastToRole(string $role, string $type, string $title, ?string $message = null, ?string $url = null): void
    {
        Utilisateur::where('role', $role)->where('is_active', true)->each(function ($u) use ($type, $title, $message, $url) {
            self::send($u, $type, $title, $message, $url);
        });
    }
}
