<?php

namespace App\Http\Controllers;

use App\Models\Candidature;
use App\Models\Offre;
use App\Models\Stage;
use App\Services\ActivityLogger;
use App\Services\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CandidatureController extends Controller
{
    public function store(Offre $offre, Request $request)
    {
        $utilisateur = $request->user();
        abort_unless($utilisateur->isEtudiant(), 403);
        abort_unless($offre->status === Offre::STATUS_PUBLISHED, 422, "L'offre n'est pas publiée.");

        $data = $request->validate([
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $app = Candidature::firstOrCreate(
            ['offer_id' => $offre->id, 'student_id' => $utilisateur->id],
            ['message' => $data['message'] ?? null, 'status' => Candidature::STATUS_PENDING],
        );

        ActivityLogger::log('application.created', ['offer_id' => $offre->id, 'application_id' => $app->id]);
        Notify::send($offre->company_id, 'application.new',
            "Nouvelle candidature : {$offre->titre}",
            "{$utilisateur->name} a postulé à votre offre.",
            route('candidatures.recues'));

        return redirect()->route('candidatures.miennes')->with('status', 'Candidature envoyée.');
    }

    /** Étudiant : ses candidatures. */
    public function mine(Request $request)
    {
        $candidatures = Candidature::with(['offer.company.companyProfile', 'stage'])
            ->where('student_id', $request->user()->id)
            ->latest()
            ->paginate(15);
        return view('candidatures.miennes', compact('candidatures'));
    }

    /** Entreprise : candidatures reçues sur ses offres. */
    public function forCompany(Request $request)
    {
        $candidatures = Candidature::with(['offer', 'student.studentProfile.formation'])
            ->whereHas('offer', fn ($q) => $q->where('company_id', $request->user()->id))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(20);
        return view('candidatures.recues', compact('candidatures'));
    }

    public function decide(Candidature $candidature, Request $request)
    {
        $utilisateur = $request->user();
        abort_unless($utilisateur->id === $candidature->offer->company_id || $utilisateur->isAdmin(), 403);

        $data = $request->validate([
            'decision' => ['required', 'in:accept,reject'],
            'decision_comment' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($candidature, $data, $utilisateur) {
            $candidature->update([
                'status' => $data['decision'] === 'accept' ? Candidature::STATUS_ACCEPTED : Candidature::STATUS_REJECTED,
                'decision_comment' => $data['decision_comment'] ?? null,
                'decided_by' => $utilisateur->id,
                'decided_at' => now(),
            ]);

            if ($data['decision'] === 'accept') {
                // Reject other pending applications by same student on same offer (none possible since unique), but reject other students' apps for same offer is a business choice — skip.
                // Auto-create Stage in 'brouillon'
                $offre = $candidature->offer;
                $debut = $offre->date_debut ?? now()->addWeek()->toDateString();
                $fin = optional($offre->date_debut)->copy()?->addWeeks($offre->duree_semaines)
                    ?? now()->addWeek()->addWeeks($offre->duree_semaines)->toDateString();
                Stage::create([
                    'application_id' => $candidature->id,
                    'offer_id' => $offre->id,
                    'student_id' => $candidature->student_id,
                    'company_id' => $offre->company_id,
                    'date_debut' => $debut,
                    'date_fin' => $fin,
                    'status' => Stage::STATUS_BROUILLON,
                ]);
            }
        });

        ActivityLogger::log('application.'.$data['decision'], ['application_id' => $candidature->id]);

        Notify::send($candidature->student_id,
            'application.'.$data['decision'],
            "Candidature {$data['decision']}ée : {$candidature->offer->titre}",
            $data['decision_comment'] ?? null,
            route('candidatures.miennes'));

        if ($data['decision'] === 'accept') {
            // Notify admins to assign tutor
            Notify::broadcastToRole('admin', 'stage.created',
                "Nouveau stage à affecter : {$candidature->offer->titre}",
                "Affecter un tuteur à {$candidature->student->name}.",
                route('admin.stages.index'));
        }

        return back()->with('status', 'Décision enregistrée.');
    }

    public function withdraw(Candidature $candidature, Request $request)
    {
        abort_unless($candidature->student_id === $request->user()->id, 403);
        abort_unless($candidature->status === Candidature::STATUS_PENDING, 422);
        $candidature->update(['status' => Candidature::STATUS_WITHDRAWN]);
        ActivityLogger::log('application.withdrawn', ['application_id' => $candidature->id]);
        return back()->with('status', 'Candidature retirée.');
    }
}
