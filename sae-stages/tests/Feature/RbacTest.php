<?php

namespace Tests\Feature;

use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): Utilisateur
    {
        return Utilisateur::create([
            'name' => $role,
            'email' => $role.'@x.com',
            'password' => Hash::make('x'),
            'role' => $role,
            'is_active' => true,
        ]);
    }

    public function test_only_admin_reaches_admin_users_page(): void
    {
        $this->actingAs($this->user(Utilisateur::ROLE_ETUDIANT))->get('/admin/utilisateurs')->assertForbidden();
        $this->actingAs($this->user(Utilisateur::ROLE_ENTREPRISE))->get('/admin/utilisateurs')->assertForbidden();
        $this->actingAs($this->user(Utilisateur::ROLE_ADMIN))->get('/admin/utilisateurs')->assertOk();
    }

    public function test_only_company_reaches_my_offers(): void
    {
        $this->actingAs($this->user(Utilisateur::ROLE_ETUDIANT))->get('/mes-offres')->assertForbidden();
        $this->actingAs($this->user(Utilisateur::ROLE_ENTREPRISE))->get('/mes-offres')->assertOk();
    }

    public function test_only_student_can_request_formation(): void
    {
        $this->actingAs($this->user(Utilisateur::ROLE_ENTREPRISE))->get('/formations/demande')->assertForbidden();
        $this->actingAs($this->user(Utilisateur::ROLE_ETUDIANT))->get('/formations/demande')->assertOk();
    }
}
