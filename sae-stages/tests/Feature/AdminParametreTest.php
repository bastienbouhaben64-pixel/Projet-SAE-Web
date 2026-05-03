<?php

namespace Tests\Feature;

use App\Models\Utilisateur;
use App\Services\Parametres;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminParametreTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Utilisateur
    {
        return Utilisateur::create([
            'name' => 'Admin',
            'email' => 'admin@test.local',
            'password' => Hash::make('secret123'),
            'role' => Utilisateur::ROLE_ADMIN,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_settings_page(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.parametres.edit'))
            ->assertOk()
            ->assertSee('Authentification à deux facteurs par email');
    }

    public function test_admin_can_enable_and_disable_email_2fa(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('admin.parametres.maj'), ['email_2fa_enabled' => '1'])
            ->assertRedirect();

        $this->assertTrue(Parametres::email2faEnabled());

        $this->actingAs($admin)
            ->put(route('admin.parametres.maj'), ['email_2fa_enabled' => '0'])
            ->assertRedirect();

        $this->assertFalse(Parametres::email2faEnabled());
    }
}
