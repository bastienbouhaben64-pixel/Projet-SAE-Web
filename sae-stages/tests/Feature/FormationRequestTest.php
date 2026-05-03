<?php

namespace Tests\Feature;

use App\Models\Formation;
use App\Models\DemandeFormation;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FormationRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_request_and_admin_approves_creates_formation(): void
    {
        $etudiant = Utilisateur::create(['name' => 'S', 'email' => 's@x.com', 'password' => Hash::make('x'), 'role' => Utilisateur::ROLE_ETUDIANT, 'is_active' => true]);
        $admin = Utilisateur::create(['name' => 'A', 'email' => 'a@x.com', 'password' => Hash::make('x'), 'role' => Utilisateur::ROLE_ADMIN, 'is_active' => true]);

        $this->actingAs($etudiant)->post('/formations/demande', [
            'intitule' => 'Master Robotique',
            'justification' => 'Spécialité non listée',
        ])->assertRedirect();

        $req = DemandeFormation::firstOrFail();
        $this->assertSame('pending', $req->status);

        $this->actingAs($admin)->post(route('admin.demandes_formation.decider', $req), [
            'decision' => 'approve',
        ])->assertRedirect();

        $this->assertSame('approved', $req->fresh()->status);
        $this->assertDatabaseHas('formations', ['intitule' => 'Master Robotique', 'is_active' => 1]);
    }
}
