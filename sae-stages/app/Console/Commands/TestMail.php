<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class TestMail extends Command
{
    protected $signature = 'mail:test {email}';
    protected $description = 'Envoie un email de test pour vérifier la configuration SMTP.';

    public function handle(): int
    {
        $email = (string) $this->argument('email');

        try {
            Mail::raw('Email de test SAE Stages envoyé avec succès.', function ($message) use ($email) {
                $message->to($email)->subject('Test email SAE Stages');
            });
        } catch (Throwable $e) {
            $this->error('Échec de l’envoi email : '.$e->getMessage());
            return self::FAILURE;
        }

        $this->info('Email de test envoyé à '.$email.'.');
        return self::SUCCESS;
    }
}
