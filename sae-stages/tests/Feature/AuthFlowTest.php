<?php

namespace Tests\Feature;

use App\Models\CodeOtp;
use App\Models\Utilisateur;
use App\Services\Parametres;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_without_2fa_succeeds_by_default(): void
    {
        Mail::fake();

        $utilisateur = Utilisateur::create([
            'name' => 'Test', 'email' => 't@x.com',
            'password' => Hash::make('secret123'), 'role' => Utilisateur::ROLE_ETUDIANT, 'is_active' => true,
        ]);

        $this->post('/connexion', ['email' => 't@x.com', 'password' => 'secret123', 'role' => 'etudiant'])
            ->assertRedirect('/tableau-de-bord');

        $this->assertAuthenticatedAs($utilisateur->fresh());
        $this->assertDatabaseCount('codes_otp', 0);
        Mail::assertNothingSent();
    }

    public function test_login_with_password_then_otp_succeeds_when_2fa_is_enabled(): void
    {
        Mail::fake();
        Parametres::set('email_2fa_enabled', true);

        $utilisateur = Utilisateur::create([
            'name' => 'Test', 'email' => 't@x.com',
            'password' => Hash::make('secret123'), 'role' => Utilisateur::ROLE_ETUDIANT, 'is_active' => true,
        ]);

        $this->post('/connexion', ['email' => 't@x.com', 'password' => 'secret123', 'role' => 'etudiant'])
            ->assertRedirect('/otp');

        $this->assertDatabaseCount('codes_otp', 1);
        Mail::assertSent(\App\Mail\OtpMail::class);

        // Force a known code by re-issuing via service in test (we don't know the random one)
        // So we directly stub: capture the plain code from log isn't possible; instead use service to verify by hashing.
        // Trick: replace OTP with a known one
        $otp = CodeOtp::first();
        $otp->update(['code_hash' => Hash::make('123456')]);

        $this->post('/otp', ['code' => '123456'])->assertRedirect('/tableau-de-bord');

        $this->assertAuthenticatedAs($utilisateur->fresh());
    }

    public function test_inactive_user_cannot_login(): void
    {
        Utilisateur::create([
            'name' => 'Inactive', 'email' => 'i@x.com',
            'password' => Hash::make('secret123'), 'role' => Utilisateur::ROLE_ETUDIANT, 'is_active' => false,
        ]);

        $this->post('/connexion', ['email' => 'i@x.com', 'password' => 'secret123', 'role' => 'etudiant'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_wrong_password_fails(): void
    {
        Utilisateur::create([
            'name' => 'X', 'email' => 'x@x.com',
            'password' => Hash::make('secret123'), 'role' => Utilisateur::ROLE_ETUDIANT, 'is_active' => true,
        ]);

        $this->post('/connexion', ['email' => 'x@x.com', 'password' => 'wrong', 'role' => 'etudiant'])
            ->assertSessionHasErrors('email');
    }

    public function test_role_mismatch_is_rejected(): void
    {
        Utilisateur::create([
            'name' => 'Etu', 'email' => 'e@x.com',
            'password' => Hash::make('secret123'),
            'role' => Utilisateur::ROLE_ETUDIANT, 'is_active' => true,
        ]);

        // Tente de se connecter en sélectionnant le rôle admin
        $this->post('/connexion', ['email' => 'e@x.com', 'password' => 'secret123', 'role' => 'admin'])
            ->assertSessionHasErrors('role');
        $this->assertGuest();
        $this->assertDatabaseCount('codes_otp', 0);
    }
}
