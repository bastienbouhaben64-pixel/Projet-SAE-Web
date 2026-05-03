<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use App\Services\ActivityLogger;
use App\Services\Notify;
use Illuminate\Http\Request;

class ConventionController extends Controller
{
    public function show(Stage $stage, Request $request)
    {
        $this->authorizeView($stage, $request);
        $stage->loadMissing('convention', 'student', 'company.companyProfile', 'tutor', 'offer');
        $convention = $stage->convention ?: $stage->convention()->create([]);
        return view('conventions.afficher', compact('stage', 'convention'));
    }

    public function updateContent(Stage $stage, Request $request)
    {
        $this->authorizeEdit($stage, $request);
        $data = $request->validate(['contenu' => ['nullable', 'string', 'max:8000']]);
        $convention = $stage->convention ?: $stage->convention()->create([]);
        $convention->update(['contenu' => $data['contenu']]);
        ActivityLogger::log('convention.content_updated', ['stage_id' => $stage->id]);
        return back()->with('status', 'Convention mise à jour.');
    }

    public function sign(Stage $stage, Request $request)
    {
        $u = $request->user();
        $convention = $stage->convention ?: $stage->convention()->create([]);

        $field = match (true) {
            $u->id === $stage->student_id => 'signed_student_at',
            $u->id === $stage->company_id => 'signed_company_at',
            $u->id === $stage->tutor_id => 'signed_tutor_at',
            default => null,
        };
        abort_unless($field, 403, "Vous n'êtes pas signataire de cette convention.");

        if ($convention->{$field}) {
            return back()->with('status', 'Vous avez déjà signé.');
        }

        $convention->update([$field => now()]);
        ActivityLogger::log('convention.signed', ['stage_id' => $stage->id, 'role' => $u->role]);

        // Notify admins when fully signed (awaiting validation)
        if ($convention->refresh()->isFullySigned() && ! $convention->isAdminValidated()) {
            Notify::broadcastToRole('admin', 'convention.fully_signed',
                "Convention à valider : {$stage->offer->titre}", null,
                route('conventions.afficher', $stage));
        }

        return back()->with('status', 'Signature enregistrée.');
    }

    public function adminValidate(Stage $stage, Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);
        $convention = $stage->convention;
        abort_unless($convention && $convention->isFullySigned(), 422, 'Convention non entièrement signée.');

        $convention->update([
            'validated_admin_at' => now(),
            'validated_admin_by' => $request->user()->id,
        ]);

        $stage->update(['status' => Stage::STATUS_EN_COURS]);

        ActivityLogger::log('convention.admin_validated', ['stage_id' => $stage->id]);

        // notify all parties
        foreach (array_filter([$stage->student_id, $stage->company_id, $stage->tutor_id]) as $uid) {
            Notify::send($uid, 'convention.validated',
                "Convention validée — stage en cours", null, route('stages.afficher', $stage));
        }

        return back()->with('status', 'Convention validée — stage démarré.');
    }

    private function authorizeView(Stage $stage, Request $request): void
    {
        $u = $request->user();
        $ok = $u->isAdmin() || $u->isJury()
            || $stage->student_id === $u->id
            || $stage->company_id === $u->id
            || $stage->tutor_id === $u->id;
        abort_unless($ok, 403);
    }

    private function authorizeEdit(Stage $stage, Request $request): void
    {
        $u = $request->user();
        $ok = $u->isAdmin() || $u->id === $stage->tutor_id || $u->id === $stage->company_id;
        abort_unless($ok, 403);
    }
}
