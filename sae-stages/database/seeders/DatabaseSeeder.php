<?php

namespace Database\Seeders;

use App\Models\Candidature;
use App\Models\Convention;
use App\Models\Formation;
use App\Models\Notification;
use App\Models\Offre;
use App\Models\ProfilEntreprise;
use App\Models\ProfilEtudiant;
use App\Models\Stage;
use App\Models\Utilisateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $formations = collect([
            ['code' => 'INFO-L3', 'intitule' => 'Licence 3 Informatique'],
            ['code' => 'MIAGE-M1', 'intitule' => 'Master 1 MIAGE'],
            ['code' => 'CYBER-M2', 'intitule' => 'Master 2 Cybersécurité'],
        ])->map(fn ($f) => Formation::create($f + ['is_active' => true]));

        // Admin
        Utilisateur::create([
            'name' => 'Admin SAE',
            'email' => 'admin@sae.local',
            'password' => Hash::make('password'),
            'role' => Utilisateur::ROLE_ADMIN,
            'is_active' => true,
        ]);

        // Étudiant
        $etu = Utilisateur::create([
            'name' => 'Alice Étudiante',
            'email' => 'etudiant@sae.local',
            'password' => Hash::make('password'),
            'role' => Utilisateur::ROLE_ETUDIANT,
            'is_active' => true,
        ]);
        ProfilEtudiant::create([
            'user_id' => $etu->id,
            'formation_id' => $formations[0]->id,
            'promo' => '2025',
            'telephone' => '0600000000',
        ]);

        // Professeur
        Utilisateur::create([
            'name' => 'Bob Professeur',
            'email' => 'prof@sae.local',
            'password' => Hash::make('password'),
            'role' => Utilisateur::ROLE_PROFESSEUR,
            'is_active' => true,
        ]);

        // Jury
        Utilisateur::create([
            'name' => 'Carla Jury',
            'email' => 'jury@sae.local',
            'password' => Hash::make('password'),
            'role' => Utilisateur::ROLE_JURY,
            'is_active' => true,
        ]);

        // Entreprise
        $ent = Utilisateur::create([
            'name' => 'Daniel Entreprise',
            'email' => 'entreprise@sae.local',
            'password' => Hash::make('password'),
            'role' => Utilisateur::ROLE_ENTREPRISE,
            'is_active' => true,
        ]);
        ProfilEntreprise::create([
            'user_id' => $ent->id,
            'raison_sociale' => 'Acme Tech',
            'siret' => '12345678901234',
            'secteur' => 'Informatique',
            'site_web' => 'https://acme.example',
            'adresse' => '12 rue du Code, 75000 Paris',
            'is_validated' => true,
        ]);

        $offresData = [
            ['titre' => 'Stage Développeur Web Full-Stack', 'lieu' => 'Paris', 'duree_semaines' => 12, 'domaine' => 'Web', 'formation_id' => $formations[1]->id],
            ['titre' => 'Stage Cybersécurité (Pentest)', 'lieu' => 'Lyon', 'duree_semaines' => 16, 'domaine' => 'Cybersécurité', 'formation_id' => $formations[2]->id],
            ['titre' => 'Stage Data Engineer', 'lieu' => 'Remote', 'duree_semaines' => 24, 'domaine' => 'Data', 'formation_id' => $formations[1]->id],
        ];
        $offresCreated = [];
        foreach ($offresData as $o) {
            $offresCreated[] = Offre::create($o + [
                'company_id' => $ent->id,
                'description' => "Mission de stage chez Acme Tech.\n\nResponsabilités, technologies utilisées, environnement de travail, etc.",
                'date_debut' => now()->addMonth()->toDateString(),
                'remuneration' => '1000€ / mois',
                'status' => Offre::STATUS_PUBLISHED,
            ]);
        }

        // ---- Phase 2 : exemple de candidature acceptée + stage en cours
        $prof = Utilisateur::where('email', 'prof@sae.local')->first();
        $candidature = Candidature::create([
            'offer_id' => $offresCreated[0]->id,
            'student_id' => $etu->id,
            'message' => "Très intéressé par cette offre, j'ai déjà travaillé sur des projets Laravel.",
            'status' => Candidature::STATUS_ACCEPTED,
            'decision_comment' => 'Profil intéressant, accepté.',
            'decided_by' => $ent->id,
            'decided_at' => now()->subDays(7),
        ]);

        $stage = Stage::create([
            'application_id' => $candidature->id,
            'offer_id' => $offresCreated[0]->id,
            'student_id' => $etu->id,
            'company_id' => $ent->id,
            'tutor_id' => $prof->id,
            'date_debut' => now()->subDays(15)->toDateString(),
            'date_fin' => now()->addWeeks(10)->toDateString(),
            'status' => Stage::STATUS_EN_COURS,
        ]);

        Convention::create([
            'stage_id' => $stage->id,
            'contenu' => "Convention de stage entre l'étudiant, l'entreprise Acme Tech et CY Tech.\n\nObjectif du stage : développement web full-stack.",
            'signed_student_at' => now()->subDays(14),
            'signed_company_at' => now()->subDays(13),
            'signed_tutor_at' => now()->subDays(12),
            'validated_admin_at' => now()->subDays(11),
            'validated_admin_by' => Utilisateur::where('email', 'admin@sae.local')->value('id'),
        ]);

        Notification::create([
            'user_id' => $etu->id,
            'type' => 'demo',
            'title' => 'Bienvenue sur SAE Stages',
            'message' => 'Votre stage chez Acme Tech a démarré. Consultez vos missions et alimentez votre cahier de stage.',
            'url' => route('stages.afficher', $stage),
        ]);

        $this->seedDemoSupplementaire($formations, $prof);
    }

    private function seedDemoSupplementaire($formations, $prof): void
    {
        // Entreprise validée supplémentaire
        $ent2 = Utilisateur::create([
            'name' => 'Élise CyberSec', 'email' => 'cybersec@sae.local',
            'password' => Hash::make('password'), 'role' => Utilisateur::ROLE_ENTREPRISE, 'is_active' => true,
        ]);
        ProfilEntreprise::create([
            'user_id' => $ent2->id, 'raison_sociale' => 'CyberSec Solutions', 'siret' => '99887766554433',
            'secteur' => 'Cybersécurité', 'site_web' => 'https://cybersec.example',
            'adresse' => '5 av. de Lyon, 69000 Lyon', 'is_validated' => true,
        ]);

        // Entreprise en attente de validation
        $ent3 = Utilisateur::create([
            'name' => 'Frank Startup', 'email' => 'startup@sae.local',
            'password' => Hash::make('password'), 'role' => Utilisateur::ROLE_ENTREPRISE, 'is_active' => false,
        ]);
        ProfilEntreprise::create([
            'user_id' => $ent3->id, 'raison_sociale' => 'NewWave SAS',
            'secteur' => 'Data', 'is_validated' => false,
        ]);

        // 3 étudiants supplémentaires
        $etudiants = [];
        foreach ([
            ['Hugo Dev', 'hugo@sae.local', 0],
            ['Inès Crypto', 'ines@sae.local', 2],
            ['Karim Data', 'karim@sae.local', 1],
        ] as [$name, $email, $formationIdx]) {
            $u = Utilisateur::create([
                'name' => $name, 'email' => $email,
                'password' => Hash::make('password'),
                'role' => Utilisateur::ROLE_ETUDIANT, 'is_active' => true,
            ]);
            ProfilEtudiant::create([
                'user_id' => $u->id, 'formation_id' => $formations[$formationIdx]->id,
                'promo' => '2025', 'telephone' => '0600000000',
            ]);
            $etudiants[] = $u;
        }

        // Offres supplémentaires
        $o2 = Offre::create([
            'company_id' => $ent2->id, 'titre' => 'Stage Pentest junior',
            'description' => 'Audit de sécurité, tests d\'intrusion.',
            'lieu' => 'Lyon', 'duree_semaines' => 16, 'domaine' => 'Cybersécurité',
            'formation_id' => $formations[2]->id,
            'date_debut' => now()->addMonth()->toDateString(),
            'remuneration' => '1100€ / mois', 'status' => Offre::STATUS_PUBLISHED,
        ]);
        $o3 = Offre::create([
            'company_id' => $ent2->id, 'titre' => 'Stage SOC analyst',
            'description' => 'Surveillance d\'incidents, SIEM.',
            'lieu' => 'Lyon', 'duree_semaines' => 20, 'domaine' => 'Cybersécurité',
            'formation_id' => $formations[2]->id,
            'date_debut' => now()->addMonths(2)->toDateString(),
            'remuneration' => '1200€ / mois', 'status' => Offre::STATUS_PUBLISHED,
        ]);

        $admin = Utilisateur::where('role', Utilisateur::ROLE_ADMIN)->first();

        // Stage validé + archivé pour Hugo
        $candHugo = Candidature::create([
            'offer_id' => Offre::first()->id, 'student_id' => $etudiants[0]->id,
            'message' => 'Candidature de Hugo.', 'status' => Candidature::STATUS_ACCEPTED,
            'decided_by' => Offre::first()->company_id, 'decided_at' => now()->subMonths(8),
        ]);
        $stHugo = Stage::create([
            'application_id' => $candHugo->id, 'offer_id' => Offre::first()->id,
            'student_id' => $etudiants[0]->id, 'company_id' => Offre::first()->company_id,
            'tutor_id' => $prof->id,
            'date_debut' => now()->subMonths(8)->toDateString(),
            'date_fin' => now()->subMonths(5)->toDateString(),
            'status' => Stage::STATUS_VALIDE,
            'jury_id' => Utilisateur::where('role', Utilisateur::ROLE_JURY)->value('id'),
            'jury_comment' => 'Très bon stage, autonomie remarquable.',
            'jury_note' => 17.5,
            'jury_grille' => ['technique' => 4, 'autonomie' => 5, 'communication' => 4, 'integration' => 5, 'qualite_ecrit' => 4, 'soutenance' => 4],
            'validated_at' => now()->subMonths(5),
            'archived_at' => now()->subMonths(4),
        ]);
        Convention::create([
            'stage_id' => $stHugo->id, 'contenu' => 'Convention archivée.',
            'signed_student_at' => now()->subMonths(8), 'signed_company_at' => now()->subMonths(8),
            'signed_tutor_at' => now()->subMonths(8), 'validated_admin_at' => now()->subMonths(8),
            'validated_admin_by' => $admin->id,
        ]);

        // Stage en cours pour Inès
        $candInes = Candidature::create([
            'offer_id' => $o2->id, 'student_id' => $etudiants[1]->id,
            'message' => 'Très motivée par le pentest.', 'status' => Candidature::STATUS_ACCEPTED,
            'decided_by' => $ent2->id, 'decided_at' => now()->subWeeks(3),
        ]);
        $stInes = Stage::create([
            'application_id' => $candInes->id, 'offer_id' => $o2->id,
            'student_id' => $etudiants[1]->id, 'company_id' => $ent2->id,
            'tutor_id' => $prof->id,
            'date_debut' => now()->subWeeks(2)->toDateString(),
            'date_fin' => now()->addWeeks(14)->toDateString(),
            'status' => Stage::STATUS_EN_COURS,
        ]);
        Convention::create([
            'stage_id' => $stInes->id,
            'signed_student_at' => now()->subWeeks(3), 'signed_company_at' => now()->subWeeks(3),
            'signed_tutor_at' => now()->subWeeks(2), 'validated_admin_at' => now()->subWeeks(2),
            'validated_admin_by' => $admin->id,
        ]);

        // Stage en attente de tuteur pour Karim (convention partielle)
        $candKarim = Candidature::create([
            'offer_id' => $o3->id, 'student_id' => $etudiants[2]->id,
            'message' => 'Intérêt pour la data.', 'status' => Candidature::STATUS_ACCEPTED,
            'decided_by' => $ent2->id, 'decided_at' => now()->subDays(5),
        ]);
        $stKarim = Stage::create([
            'application_id' => $candKarim->id, 'offer_id' => $o3->id,
            'student_id' => $etudiants[2]->id, 'company_id' => $ent2->id,
            'date_debut' => now()->addWeeks(4)->toDateString(),
            'date_fin' => now()->addMonths(6)->toDateString(),
            'status' => Stage::STATUS_BROUILLON,
        ]);
        Convention::create([
            'stage_id' => $stKarim->id,
            'signed_student_at' => now()->subDays(2),
            // entreprise et tuteur pas encore signés
        ]);

        // Quelques candidatures supplémentaires en attente / refusées
        Candidature::create([
            'offer_id' => $o2->id, 'student_id' => $etudiants[0]->id,
            'message' => 'Curiosité pour la cyber.', 'status' => Candidature::STATUS_PENDING,
        ]);
        Candidature::create([
            'offer_id' => $o3->id, 'student_id' => $etudiants[1]->id,
            'message' => 'Intéressée par le SOC.', 'status' => Candidature::STATUS_REJECTED,
            'decision_comment' => 'Profil ne correspond pas.', 'decided_by' => $ent2->id, 'decided_at' => now()->subDays(2),
        ]);

        // Notification pour l'admin
        Notification::create([
            'user_id' => $admin->id, 'type' => 'admin.entreprises',
            'title' => '1 entreprise en attente de validation',
            'message' => 'NewWave SAS attend votre validation.',
            'url' => route('admin.entreprises.index'),
        ]);
    }
}
