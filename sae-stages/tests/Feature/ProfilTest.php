<?php

namespace Tests\Feature;

use App\Models\Formation;
use App\Models\ProfilEntreprise;
use App\Models\ProfilEtudiant;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): Utilisateur
    {
        return Utilisateur::create([
            'name' => $role, 'email' => $role.'@x.com',
            'password' => Hash::make('x'), 'role' => $role, 'is_active' => true,
        ]);
    }

    public function test_etudiant_can_update_profile_and_upload_cv(): void
    {
        Storage::fake('public');
        $u = $this->user('etudiant');
        $f = Formation::create(['code' => 'TEST', 'intitule' => 'Test Formation', 'is_active' => true]);

        $cv = UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf');

        $this->actingAs($u)->put(route('profil.etudiant'), [
            'formation_id' => $f->id,
            'promo' => '2025-2026',
            'telephone' => '0612345678',
            'cv' => $cv,
        ])->assertRedirect();

        $profil = ProfilEtudiant::where('user_id', $u->id)->firstOrFail();
        $this->assertSame($f->id, $profil->formation_id);
        $this->assertSame('2025-2026', $profil->promo);
        $this->assertNotNull($profil->cv_path);
        Storage::disk('public')->assertExists($profil->cv_path);

        // Suppression du CV
        $this->actingAs($u)->delete(route('profil.cv.supprimer'))->assertRedirect();
        $this->assertNull($profil->fresh()->cv_path);
    }

    public function test_entreprise_can_update_profile(): void
    {
        $u = $this->user('entreprise');
        $this->actingAs($u)->put(route('profil.entreprise'), [
            'raison_sociale' => 'Foo SARL',
            'siret' => '12345678901234',
            'secteur' => 'Web',
            'site_web' => 'https://foo.example',
            'adresse' => '1 rue X, Paris',
        ])->assertRedirect();

        $this->assertDatabaseHas('profils_entreprises', [
            'user_id' => $u->id, 'raison_sociale' => 'Foo SARL', 'siret' => '12345678901234',
        ]);
    }

    public function test_account_update_changes_name_email_and_optionally_password(): void
    {
        $u = $this->user('etudiant');
        $this->actingAs($u)->put(route('profil.compte'), [
            'name' => 'Nouveau Nom',
            'email' => 'nouveau@x.com',
            'password' => 'newsecret123',
            'password_confirmation' => 'newsecret123',
        ])->assertRedirect();

        $u->refresh();
        $this->assertSame('Nouveau Nom', $u->name);
        $this->assertSame('nouveau@x.com', $u->email);
        $this->assertTrue(Hash::check('newsecret123', $u->password));
    }

    public function test_professeur_can_update_personal_profile(): void
    {
        $u = $this->user('professeur');
        $this->actingAs($u)->put(route('profil.personnel'), [
            'telephone' => '0612345678',
            'specialites' => 'Web, Cybersécurité',
            'bio' => 'Enseignant chercheur depuis 10 ans.',
            'disponible' => '1',
        ])->assertRedirect();
        $u->refresh();
        $this->assertSame('Web, Cybersécurité', $u->specialites);
        $this->assertTrue($u->disponible);
    }

    public function test_etudiant_cannot_post_personal_profile(): void
    {
        $u = $this->user('etudiant');
        $this->actingAs($u)->put(route('profil.personnel'), ['bio' => 'x'])->assertForbidden();
    }

    public function test_etudiant_cannot_post_to_entreprise_profile_route(): void
    {
        $u = $this->user('etudiant');
        $this->actingAs($u)->put(route('profil.entreprise'), [
            'raison_sociale' => 'Hack',
        ])->assertForbidden();
    }
}
