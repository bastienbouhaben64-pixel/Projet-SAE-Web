<?php

namespace Tests\Feature;

use App\Models\Candidature;
use App\Models\Notification;
use App\Models\Offre;
use App\Models\Stage;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CandidatureStageTest extends TestCase
{
    use RefreshDatabase;

    private function userOf(string $role, string $email): Utilisateur
    {
        return Utilisateur::create([
            'name' => $role, 'email' => $email,
            'password' => Hash::make('x'), 'role' => $role, 'is_active' => true,
        ]);
    }

    public function test_full_flow_application_stage_convention_validation(): void
    {
        $student = $this->userOf('etudiant', 's@x.com');
        $company = $this->userOf('entreprise', 'c@x.com');
        $tutor = $this->userOf('professeur', 't@x.com');
        $admin = $this->userOf('admin', 'a@x.com');
        $jury = $this->userOf('jury', 'j@x.com');

        $offer = Offre::create([
            'company_id' => $company->id, 'titre' => 'Stage X',
            'description' => '...', 'lieu' => 'Paris', 'duree_semaines' => 12,
            'status' => 'published',
        ]);

        // 1) Étudiant postule
        $this->actingAs($student)->post("/offres/{$offer->id}/postuler", [
            'message' => 'Motivé',
        ])->assertRedirect();
        $candidature = Candidature::firstOrFail();
        $this->assertSame('pending', $candidature->status);

        // notification entreprise
        $this->assertTrue(Notification::where('user_id', $company->id)->exists());

        // 2) Entreprise accepte → stage créé
        $this->actingAs($company)->post(route('candidatures.decider', $candidature), [
            'decision' => 'accept',
        ])->assertRedirect();
        $candidature->refresh();
        $this->assertSame('accepted', $candidature->status);
        $stage = Stage::firstOrFail();
        $this->assertSame($student->id, $stage->student_id);
        $this->assertSame('brouillon', $stage->status);

        // 3) Admin affecte tuteur
        $this->actingAs($admin)->post(route('admin.stages.affecter_tuteur', $stage), [
            'tutor_id' => $tutor->id,
        ])->assertRedirect();
        $stage->refresh();
        $this->assertSame($tutor->id, $stage->tutor_id);
        $this->assertSame('convention', $stage->status);
        $this->assertNotNull($stage->convention);

        // 4) Trois parties signent
        foreach ([$student, $company, $tutor] as $u) {
            $this->actingAs($u)->post(route('conventions.signer', $stage))->assertRedirect();
        }
        $this->assertTrue($stage->convention->fresh()->isFullySigned());

        // 5) Admin valide → stage en cours
        $this->actingAs($admin)->post(route('admin.conventions.valider', $stage))->assertRedirect();
        $stage->refresh();
        $this->assertSame('en_cours', $stage->status);

        // 6) Étudiant termine
        $this->actingAs($student)->post(route('stages.terminer', $stage))->assertRedirect();
        $this->assertSame('termine', $stage->fresh()->status);

        // 7) Jury valide avec grille d'évaluation
        $this->actingAs($jury)->post(route('stages.valider_jury', $stage), [
            'jury_comment' => 'Excellent travail',
            'criteres' => [
                'technique' => 5,
                'autonomie' => 4,
                'communication' => 4,
                'integration' => 5,
                'qualite_ecrit' => 4,
                'soutenance' => 4,
            ],
        ])->assertRedirect();
        $stage->refresh();
        $this->assertSame('valide', $stage->status);
        $this->assertSame($jury->id, $stage->jury_id);
        $this->assertNotNull($stage->validated_at);
        $this->assertSame(5, $stage->jury_grille['technique']);
        // Moyenne (5+4+4+5+4+4)/6 = 4.333… × 4 = 17.33
        $this->assertEqualsWithDelta(17.33, (float) $stage->jury_note, 0.05);

        // 8) Export PDF
        $r = $this->actingAs($student)->get(route('stages.pdf', $stage));
        $r->assertOk();
        $this->assertSame('application/pdf', $r->headers->get('content-type'));
    }
}
