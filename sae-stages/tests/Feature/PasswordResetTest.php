<?php

namespace Tests\Feature;

use App\Models\Utilisateur;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private function user(): Utilisateur
    {
        return Utilisateur::create([
            'name' => 'Pwd', 'email' => 'pwd@x.com',
            'password' => Hash::make('oldsecret'),
            'role' => Utilisateur::ROLE_ETUDIANT, 'is_active' => true,
        ]);
    }

    public function test_request_form_is_accessible(): void
    {
        $this->get(route('mot_de_passe.oublie'))
            ->assertOk()
            ->assertSee('Mot de passe oublié');
    }

    public function test_request_sends_reset_notification(): void
    {
        $u = $this->user();
        Notification::fake();

        $this->post(route('mot_de_passe.envoyer_lien'), ['email' => $u->email])
            ->assertRedirect();

        Notification::assertSentTo($u, ResetPassword::class);
    }

    public function test_request_with_unknown_email_does_not_leak(): void
    {
        Notification::fake();
        $this->post(route('mot_de_passe.envoyer_lien'), ['email' => 'inconnu@x.com'])
            ->assertRedirect()
            ->assertSessionHas('status');
        Notification::assertNothingSent();
    }

    public function test_reset_with_valid_token_updates_password(): void
    {
        $u = $this->user();
        $token = Password::createToken($u);

        $this->post(route('mot_de_passe.reinitialiser'), [
            'token' => $token,
            'email' => $u->email,
            'password' => 'newsecret123',
            'password_confirmation' => 'newsecret123',
        ])->assertRedirect(route('connexion'));

        $u->refresh();
        $this->assertTrue(Hash::check('newsecret123', $u->password));
    }

    public function test_reset_with_invalid_token_fails(): void
    {
        $u = $this->user();
        $this->post(route('mot_de_passe.reinitialiser'), [
            'token' => 'bogus',
            'email' => $u->email,
            'password' => 'newsecret123',
            'password_confirmation' => 'newsecret123',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('oldsecret', $u->fresh()->password));
    }
}
