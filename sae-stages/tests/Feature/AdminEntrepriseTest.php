<?php

namespace Tests\Feature;

use App\Models\ProfilEntreprise;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminEntrepriseTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Utilisateur
    {
        return Utilisateur::create([
            'name' => 'Admin', 'email' => 'a@x.com',
            'password' => Hash::make('x'), 'role' => Utilisateur::ROLE_ADMIN, 'is_active' => true,
        ]);
    }

    private function entreprise(bool $withProfile = true, bool $active = false, bool $validated = false): Utilisateur
    {
        $u = Utilisateur::create([
            'name' => 'Entreprise X', 'email' => 'ent@x.com',
            'password' => Hash::make('x'), 'role' => Utilisateur::ROLE_ENTREPRISE, 'is_active' => $active,
        ]);
        if ($withProfile) {
            ProfilEntreprise::create([
                'user_id' => $u->id, 'raison_sociale' => 'Foo SARL', 'is_validated' => $validated,
            ]);
        }
        return $u;
    }

    public function test_admin_can_list_entreprises_a_valider(): void
    {
        $admin = $this->admin();
        $this->entreprise();
        $this->actingAs($admin)->get(route('admin.entreprises.index'))
            ->assertOk()
            ->assertSee('Foo SARL');
    }

    public function test_validate_activates_account_and_marks_profile(): void
    {
        $admin = $this->admin();
        $u = $this->entreprise();
        $this->actingAs($admin)->post(route('admin.entreprises.valider', $u))->assertRedirect();
        $u->refresh();
        $this->assertTrue($u->is_active);
        $this->assertTrue($u->profilEntreprise->fresh()->is_validated);
        $this->assertDatabaseHas('notifications', ['user_id' => $u->id, 'type' => 'compte.valide']);
    }

    public function test_validate_without_profile_returns_422(): void
    {
        $admin = $this->admin();
        $u = $this->entreprise(withProfile: false);
        $this->actingAs($admin)->post(route('admin.entreprises.valider', $u))->assertStatus(422);
    }

    public function test_reject_suspends_account(): void
    {
        $admin = $this->admin();
        $u = $this->entreprise(active: true, validated: true);
        $this->actingAs($admin)->post(route('admin.entreprises.rejeter', $u), ['motif' => 'Doublon'])->assertRedirect();
        $u->refresh();
        $this->assertFalse($u->is_active);
        $this->assertFalse($u->profilEntreprise->fresh()->is_validated);
    }

    public function test_non_admin_is_forbidden(): void
    {
        $u = $this->entreprise();
        $this->actingAs($u)->get(route('admin.entreprises.index'))->assertForbidden();
    }
}
