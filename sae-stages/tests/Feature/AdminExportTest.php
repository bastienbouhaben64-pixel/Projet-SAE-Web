<?php

namespace Tests\Feature;

use App\Models\Candidature;
use App\Models\Offre;
use App\Models\Stage;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminExportTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, string $email): Utilisateur
    {
        return Utilisateur::create([
            'name' => $role, 'email' => $email,
            'password' => Hash::make('x'), 'role' => $role, 'is_active' => true,
        ]);
    }

    public function test_csv_stages_is_downloadable(): void
    {
        $admin = $this->user('admin', 'a@x.com');
        $student = $this->user('etudiant', 'e@x.com');
        $company = $this->user('entreprise', 'c@x.com');

        $offer = Offre::create([
            'company_id' => $company->id, 'titre' => 'Stage X',
            'description' => 'Hello', 'lieu' => 'Paris', 'duree_semaines' => 12,
            'status' => 'published',
        ]);
        $cand = Candidature::create([
            'offer_id' => $offer->id, 'student_id' => $student->id,
            'message' => 'm', 'status' => Candidature::STATUS_ACCEPTED,
        ]);
        Stage::create([
            'application_id' => $cand->id, 'offer_id' => $offer->id,
            'student_id' => $student->id, 'company_id' => $company->id,
            'date_debut' => now(), 'date_fin' => now()->addMonths(3),
            'status' => Stage::STATUS_EN_COURS,
        ]);

        $r = $this->actingAs($admin)->get(route('admin.exports.stages'));
        $r->assertOk();
        $this->assertStringContainsString('text/csv', $r->headers->get('content-type'));
        $body = $r->streamedContent();
        $this->assertStringContainsString('Stage X', $body);
        $this->assertStringContainsString('e@x.com', $body);
    }

    public function test_csv_candidatures_is_downloadable(): void
    {
        $admin = $this->user('admin', 'a@x.com');
        $student = $this->user('etudiant', 'e@x.com');
        $company = $this->user('entreprise', 'c@x.com');
        $offer = Offre::create([
            'company_id' => $company->id, 'titre' => 'Stage Y', 'description' => 'd',
            'lieu' => 'Lyon', 'duree_semaines' => 8, 'status' => 'published',
        ]);
        Candidature::create([
            'offer_id' => $offer->id, 'student_id' => $student->id,
            'message' => 'm', 'status' => Candidature::STATUS_PENDING,
        ]);

        $r = $this->actingAs($admin)->get(route('admin.exports.candidatures'));
        $r->assertOk();
        $this->assertStringContainsString('Stage Y', $r->streamedContent());
    }

    public function test_non_admin_cannot_export(): void
    {
        $u = $this->user('etudiant', 'e@x.com');
        $this->actingAs($u)->get(route('admin.exports.stages'))->assertForbidden();
    }
}
