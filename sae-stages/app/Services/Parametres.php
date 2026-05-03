<?php

namespace App\Services;

use App\Models\Parametre;

class Parametres
{
    // Service centralisé pour lire/écrire les réglages globaux stockés en base.
    public static function get(string $cle, mixed $default = null): mixed
    {
        return Parametre::where('cle', $cle)->value('valeur') ?? $default;
    }

    public static function bool(string $cle, bool $default = false): bool
    {
        $valeur = self::get($cle, $default ? '1' : '0');
        return filter_var($valeur, FILTER_VALIDATE_BOOLEAN);
    }

    public static function set(string $cle, mixed $valeur): Parametre
    {
        if (is_bool($valeur)) {
            $valeur = $valeur ? '1' : '0';
        }

        return Parametre::updateOrCreate(
            ['cle' => $cle],
            ['valeur' => (string) $valeur]
        );
    }

    public static function email2faEnabled(): bool
    {
        // Désactivé par défaut pour faciliter les démonstrations en localhost.
        return self::bool('email_2fa_enabled', false);
    }
}
