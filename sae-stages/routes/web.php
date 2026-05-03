<?php

use App\Http\Controllers\Admin\TraceController;
use App\Http\Controllers\Admin\FormationController as AdminFormationController;
use App\Http\Controllers\Admin\DemandeFormationController as AdminDemandeFormationController;
use App\Http\Controllers\Admin\StageController as AdminStageController;
use App\Http\Controllers\Admin\UtilisateurController as AdminUtilisateurController;
use App\Http\Controllers\Admin\EntrepriseController as AdminEntrepriseController;
use App\Http\Controllers\Admin\ExportController as AdminExportController;
use App\Http\Controllers\Admin\ParametreController as AdminParametreController;
use App\Http\Controllers\CandidatureController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CahierController;
use App\Http\Controllers\ConventionController;
use App\Http\Controllers\TableauBordController;
use App\Http\Controllers\DemandeFormationController;
use App\Http\Controllers\GanttController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OffreController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\StageController;
use App\Http\Controllers\AccueilController;
use App\Http\Controllers\DocumentStageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AccueilController::class, 'show'])->name('accueil');

// --- Auth (invité) ---
Route::middleware('guest')->group(function () {
    Route::get('/inscription', [RegisterController::class, 'show'])->name('inscription');
    Route::post('/inscription', [RegisterController::class, 'store']);
    Route::get('/connexion', [LoginController::class, 'show'])->name('connexion');
    Route::post('/connexion', [LoginController::class, 'store']);
    Route::get('/otp', [OtpController::class, 'show'])->name('otp.afficher');
    Route::post('/otp', [OtpController::class, 'verify'])->name('otp.verifier');
    Route::post('/otp/renvoyer', [OtpController::class, 'resend'])->name('otp.renvoyer');

    Route::get('/mot-de-passe/oublie', [PasswordResetController::class, 'showRequest'])->name('mot_de_passe.oublie');
    Route::post('/mot-de-passe/oublie', [PasswordResetController::class, 'sendLink'])->name('mot_de_passe.envoyer_lien');
    Route::get('/mot-de-passe/reinitialiser/{token}', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/mot-de-passe/reinitialiser', [PasswordResetController::class, 'reset'])->name('mot_de_passe.reinitialiser');
});

Route::post('/deconnexion', [LoginController::class, 'destroy'])->middleware('auth')->name('deconnexion');

// Consultation publique : seuls la liste et le détail des offres publiées sont ouverts.
Route::get('/offres', [OffreController::class, 'index'])->name('offres.index');
Route::get('/offres/{offre}', [OffreController::class, 'show'])->name('offres.afficher');

// --- Authentifié ---
Route::middleware('auth')->group(function () {
    Route::get('/tableau-de-bord', [TableauBordController::class, 'index'])->name('tableau_bord');

    // Profil utilisateur
    Route::get('/profil', [ProfilController::class, 'show'])->name('profil.afficher');
    Route::put('/profil/compte', [ProfilController::class, 'updateAccount'])->name('profil.compte');
    Route::put('/profil/etudiant', [ProfilController::class, 'updateEtudiant'])->name('profil.etudiant');
    Route::delete('/profil/etudiant/cv', [ProfilController::class, 'deleteCv'])->name('profil.cv.supprimer');
    Route::put('/profil/entreprise', [ProfilController::class, 'updateEntreprise'])->name('profil.entreprise');
    Route::put('/profil/personnel', [ProfilController::class, 'updatePersonnel'])->name('profil.personnel');

    // Étudiants : demandes de formation
    Route::middleware('role:etudiant')->group(function () {
        Route::get('/formations/demande', [DemandeFormationController::class, 'create'])->name('demandes_formation.creer');
        Route::post('/formations/demande', [DemandeFormationController::class, 'store'])->name('demandes_formation.envoyer');
        Route::get('/formations/demandes', [DemandeFormationController::class, 'myIndex'])->name('demandes_formation.miennes');
    });

    // Entreprises : CRUD offres
    Route::middleware('role:entreprise,admin')->group(function () {
        Route::get('/mes-offres', [OffreController::class, 'myIndex'])->name('offres.miennes');
        Route::get('/mes-offres/nouvelle', [OffreController::class, 'create'])->name('offres.creer');
        Route::post('/mes-offres', [OffreController::class, 'store'])->name('offres.enregistrer');
        Route::get('/mes-offres/{offre}/modifier', [OffreController::class, 'edit'])->name('offres.modifier');
        Route::put('/mes-offres/{offre}', [OffreController::class, 'update'])->name('offres.maj');
        Route::delete('/mes-offres/{offre}', [OffreController::class, 'destroy'])->name('offres.supprimer');
        Route::post('/mes-offres/{offre}/archiver', [OffreController::class, 'archive'])->name('offres.archiver');
    });

    // Admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/utilisateurs', [AdminUtilisateurController::class, 'index'])->name('utilisateurs.index');
        Route::put('/utilisateurs/{utilisateur}', [AdminUtilisateurController::class, 'update'])->name('utilisateurs.maj');

        Route::get('/entreprises', [AdminEntrepriseController::class, 'index'])->name('entreprises.index');
        Route::post('/entreprises/{utilisateur}/valider', [AdminEntrepriseController::class, 'validate'])->name('entreprises.valider');
        Route::post('/entreprises/{utilisateur}/rejeter', [AdminEntrepriseController::class, 'reject'])->name('entreprises.rejeter');

        Route::get('/formations', [AdminFormationController::class, 'index'])->name('formations.index');
        Route::post('/formations', [AdminFormationController::class, 'store'])->name('formations.enregistrer');
        Route::put('/formations/{formation}', [AdminFormationController::class, 'update'])->name('formations.maj');
        Route::delete('/formations/{formation}', [AdminFormationController::class, 'destroy'])->name('formations.supprimer');

        Route::get('/demandes-formation', [AdminDemandeFormationController::class, 'index'])->name('demandes_formation.index');
        Route::post('/demandes-formation/{demandeFormation}', [AdminDemandeFormationController::class, 'decide'])->name('demandes_formation.decider');

        Route::get('/traces', [TraceController::class, 'index'])->name('traces.index');
        Route::get('/parametres', [AdminParametreController::class, 'edit'])->name('parametres.edit');
        Route::put('/parametres', [AdminParametreController::class, 'update'])->name('parametres.maj');

        Route::get('/exports/stages.csv', [AdminExportController::class, 'stages'])->name('exports.stages');
        Route::get('/exports/candidatures.csv', [AdminExportController::class, 'candidatures'])->name('exports.candidatures');

        Route::get('/stages', [AdminStageController::class, 'index'])->name('stages.index');
        Route::post('/stages/{stage}/tuteur', [AdminStageController::class, 'assignTutor'])->name('stages.affecter_tuteur');
        Route::post('/stages/{stage}/archiver', [AdminStageController::class, 'archive'])->name('stages.archiver');
        Route::post('/stages/{stage}/desarchiver', [AdminStageController::class, 'unarchive'])->name('stages.desarchiver');
        Route::post('/conventions/{stage}/valider', [ConventionController::class, 'adminValidate'])->name('conventions.valider');
    });

    // --- Candidatures ---
    Route::middleware('role:etudiant')->group(function () {
        Route::post('/offres/{offre}/postuler', [CandidatureController::class, 'store'])->name('candidatures.envoyer');
        Route::get('/mes-candidatures', [CandidatureController::class, 'mine'])->name('candidatures.miennes');
        Route::put('/candidatures/{candidature}/retirer', [CandidatureController::class, 'withdraw'])->name('candidatures.retirer');
    });
    Route::middleware('role:entreprise,admin')->group(function () {
        Route::get('/candidatures-recues', [CandidatureController::class, 'forCompany'])->name('candidatures.recues');
        Route::post('/candidatures/{candidature}/decision', [CandidatureController::class, 'decide'])->name('candidatures.decider');
    });

    // --- Stages ---
    Route::get('/stages', [StageController::class, 'index'])->name('stages.index');
    Route::get('/stages/{stage}', [StageController::class, 'show'])->name('stages.afficher');
    Route::get('/stages/{stage}/dossier.pdf', [StageController::class, 'exportPdf'])->name('stages.pdf');
    Route::post('/stages/{stage}/remarques', [StageController::class, 'addRemark'])->name('stages.remarques.ajouter');
    Route::post('/stages/{stage}/terminer', [StageController::class, 'markAsEnded'])->name('stages.terminer');
    Route::post('/stages/{stage}/valider-jury', [StageController::class, 'validateByJury'])
        ->name('stages.valider_jury')->middleware('role:jury,admin');

    // --- Convention ---
    Route::get('/stages/{stage}/convention', [ConventionController::class, 'show'])->name('conventions.afficher');
    Route::put('/stages/{stage}/convention', [ConventionController::class, 'updateContent'])->name('conventions.maj');
    Route::post('/stages/{stage}/convention/signer', [ConventionController::class, 'sign'])->name('conventions.signer');

    // --- Documents ---
    Route::post('/stages/{stage}/documents', [DocumentStageController::class, 'store'])->name('documents.envoyer');
    Route::get('/documents/{document}/telecharger', [DocumentStageController::class, 'download'])->name('documents.telecharger');
    Route::delete('/documents/{document}', [DocumentStageController::class, 'destroy'])->name('documents.supprimer');

    // --- Cahier ---
    Route::post('/stages/{stage}/cahier', [CahierController::class, 'store'])->name('cahier.ajouter');
    Route::delete('/cahier/{entreeCahier}', [CahierController::class, 'destroy'])->name('cahier.supprimer');

    // --- Missions ---
    Route::post('/stages/{stage}/missions', [MissionController::class, 'store'])->name('missions.creer');
    Route::put('/missions/{mission}', [MissionController::class, 'update'])->name('missions.maj');
    Route::delete('/missions/{mission}', [MissionController::class, 'destroy'])->name('missions.supprimer');

    // --- GANTT ---
    Route::get('/gantt', [GanttController::class, 'index'])->name('gantt');

    // --- Notifications ---
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/lue', [NotificationController::class, 'markRead'])->name('notifications.lire');
    Route::post('/notifications/toutes-lues', [NotificationController::class, 'markAllRead'])->name('notifications.tout_lire');
});
