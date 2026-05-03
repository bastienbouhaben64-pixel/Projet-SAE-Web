<?php

namespace Tests\Feature;

use App\Models\Offre;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OffersTest extends TestCase
{
    use RefreshDatabase;

    private function company(): Utilisateur
    {
        return Utilisateur::create([
            'name' => 'C', 'email' => 'c@x.com',
            'password' => Hash::make('x'), 'role' => Utilisateur::ROLE_ENTREPRISE, 'is_active' => true,
        ]);
    }

    private function student(): Utilisateur
    {
        return Utilisateur::create([
            'name' => 'S', 'email' => 's@x.com',
            'password' => Hash::make('x'), 'role' => Utilisateur::ROLE_ETUDIANT, 'is_active' => true,
        ]);
    }

    public function test_company_can_create_offer(): void
    {
        $c = $this->company();
        $this->actingAs($c)->post('/mes-offres', [
            'titre' => 'Stage Web', 'description' => 'desc', 'lieu' => 'Paris',
            'duree_semaines' => 12, 'status' => 'published',
        ])->assertRedirect('/mes-offres');

        $this->assertDatabaseHas('offres', ['titre' => 'Stage Web', 'company_id' => $c->id]);
    }

    public function test_search_and_filters(): void
    {
        $c = $this->company();
        Offre::create(['company_id' => $c->id, 'titre' => 'Stage Paris Web', 'description' => 'PHP', 'lieu' => 'Paris', 'duree_semaines' => 12, 'status' => 'published']);
        Offre::create(['company_id' => $c->id, 'titre' => 'Stage Lyon Data', 'description' => 'Python', 'lieu' => 'Lyon', 'duree_semaines' => 24, 'status' => 'published']);
        Offre::create(['company_id' => $c->id, 'titre' => 'Brouillon', 'description' => 'x', 'lieu' => 'Paris', 'duree_semaines' => 8, 'status' => 'draft']);

        $etudiant = $this->student();

        $r = $this->actingAs($etudiant)->withoutExceptionHandling()->get('/offres?lieu=Lyon');
        $r->assertSee('Stage Lyon Data');
        $r->assertDontSee('Stage Paris Web');

        $r = $this->actingAs($etudiant)->get('/offres?duree_min=20');
        $r->assertSee('Stage Lyon Data');
        $r->assertDontSee('Stage Paris Web');

        $r = $this->actingAs($etudiant)->get('/offres');
        $r->assertDontSee('Brouillon');
    }

    public function test_guest_can_view_published_offers(): void
    {
        $c = $this->company();
        $offre = Offre::create(['company_id' => $c->id, 'titre' => 'Stage public', 'description' => 'PHP', 'lieu' => 'Paris', 'duree_semaines' => 12, 'status' => 'published']);
        Offre::create(['company_id' => $c->id, 'titre' => 'Stage brouillon', 'description' => 'x', 'lieu' => 'Paris', 'duree_semaines' => 8, 'status' => 'draft']);

        $this->get('/offres')
            ->assertOk()
            ->assertSee('Stage public')
            ->assertDontSee('Stage brouillon');

        $this->get('/offres/'.$offre->id)
            ->assertOk()
            ->assertSee('Stage public')
            ->assertSee('connectez-vous avec un compte étudiant');
    }

    public function test_guest_cannot_apply_to_offer(): void
    {
        $c = $this->company();
        $offre = Offre::create(['company_id' => $c->id, 'titre' => 'Stage public', 'description' => 'PHP', 'lieu' => 'Paris', 'duree_semaines' => 12, 'status' => 'published']);

        $this->post('/offres/'.$offre->id.'/postuler', ['message' => 'Bonjour'])
            ->assertRedirect('/connexion');
    }
}
