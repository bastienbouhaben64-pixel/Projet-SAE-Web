<?php

namespace Tests\Feature;

use App\Mail\NotificationMail;
use App\Models\Utilisateur;
use App\Services\Notify;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_notify_send_creates_record_and_sends_email(): void
    {
        Mail::fake();
        $u = Utilisateur::create([
            'name' => 'Eve', 'email' => 'eve@x.com',
            'password' => Hash::make('x'),
            'role' => Utilisateur::ROLE_ETUDIANT, 'is_active' => true,
        ]);

        Notify::send($u, 'demo', 'Bonjour', 'Voici un message', '/url');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $u->id, 'type' => 'demo', 'title' => 'Bonjour',
        ]);
        Mail::assertSent(NotificationMail::class, function ($mail) use ($u) {
            return $mail->hasTo($u->email) && $mail->titre === 'Bonjour';
        });
    }

    public function test_notify_email_can_be_disabled(): void
    {
        Mail::fake();
        config(['notifications.email_enabled' => false]);
        $u = Utilisateur::create([
            'name' => 'Eve2', 'email' => 'eve2@x.com',
            'password' => Hash::make('x'),
            'role' => Utilisateur::ROLE_ETUDIANT, 'is_active' => true,
        ]);

        Notify::send($u, 'demo', 'Test');

        Mail::assertNothingSent();
    }
}
