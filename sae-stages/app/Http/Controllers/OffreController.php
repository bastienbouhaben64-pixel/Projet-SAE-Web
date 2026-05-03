<?php

namespace App\Http\Controllers;

use App\Models\Formation;
use App\Models\Offre;
use App\Models\Utilisateur;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OffreController extends Controller
{
    /** Liste publique (étudiants/profs/admin/jury) avec recherche + filtres. */
    public function index(Request $request)
    {
        $filters = $request->only(['q', 'lieu', 'domaine', 'formation_id', 'duree_min', 'duree_max', 'remunere', 'debut_apres', 'tri']);

        $tris = [
            'recent' => ['created_at', 'desc'],
            'ancien' => ['created_at', 'asc'],
            'duree_asc' => ['duree_semaines', 'asc'],
            'duree_desc' => ['duree_semaines', 'desc'],
            'debut' => ['date_debut', 'asc'],
        ];
        [$col, $dir] = $tris[$filters['tri'] ?? 'recent'] ?? $tris['recent'];

        $offres = Offre::with(['company.companyProfile', 'formation'])
            ->published()
            ->filter($filters)
            ->orderBy($col, $dir)
            ->paginate(12)
            ->withQueryString();

        $formations = Formation::where('is_active', true)->orderBy('intitule')->get();
        $lieuxConnus = Offre::published()->whereNotNull('lieu')->distinct()->orderBy('lieu')->pluck('lieu');
        $domainesConnus = Offre::published()->whereNotNull('domaine')->distinct()->orderBy('domaine')->pluck('domaine')->filter()->values();

        return view('offres.index', compact('offres', 'filters', 'formations', 'lieuxConnus', 'domainesConnus'));
    }

    public function show(Offre $offre)
    {
        if ($offre->status !== Offre::STATUS_PUBLISHED) {
            $utilisateur = auth()->user();
            abort_unless($utilisateur && ($utilisateur->isAdmin() || $offre->company_id === $utilisateur->id), 404);
        }
        $offre->load(['company.companyProfile', 'formation']);
        return view('offres.afficher', compact('offre'));
    }

    /** Espace entreprise. */
    public function myIndex(Request $request)
    {
        $offres = $request->user()->offers()
            ->with('formation')
            ->latest()
            ->paginate(15);
        return view('offres.miennes', compact('offres'));
    }

    public function create()
    {
        $formations = Formation::where('is_active', true)->orderBy('intitule')->get();
        return view('offres.form', ['offer' => new Offre(), 'formations' => $formations]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['company_id'] = $request->user()->id;
        $offre = Offre::create($data);
        ActivityLogger::log('offer.created', ['offer_id' => $offre->id]);
        return redirect()->route('offres.miennes')->with('status', 'Offre créée.');
    }

    public function edit(Offre $offre, Request $request)
    {
        $this->authorizeCompany($offre, $request);
        $formations = Formation::where('is_active', true)->orderBy('intitule')->get();
        return view('offres.form', compact('offre', 'formations'));
    }

    public function update(Offre $offre, Request $request)
    {
        $this->authorizeCompany($offre, $request);
        $data = $this->validateData($request);
        $offre->update($data);
        ActivityLogger::log('offer.updated', ['offer_id' => $offre->id]);
        return redirect()->route('offres.miennes')->with('status', 'Offre mise à jour.');
    }

    public function destroy(Offre $offre, Request $request)
    {
        $this->authorizeCompany($offre, $request);
        $id = $offre->id;
        $offre->delete();
        ActivityLogger::log('offer.deleted', ['offer_id' => $id]);
        return redirect()->route('offres.miennes')->with('status', 'Offre supprimée.');
    }

    public function archive(Offre $offre, Request $request)
    {
        $utilisateur = $request->user();
        abort_unless($utilisateur->isAdmin() || $offre->company_id === $utilisateur->id, 403);
        $offre->update(['status' => Offre::STATUS_ARCHIVED]);
        ActivityLogger::log('offer.archived', ['offer_id' => $offre->id]);
        return back()->with('status', 'Offre archivée.');
    }

    private function authorizeCompany(Offre $offre, Request $request): void
    {
        $utilisateur = $request->user();
        abort_unless($utilisateur && ($utilisateur->isAdmin() || $offre->company_id === $utilisateur->id), 403);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'titre' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string'],
            'lieu' => ['required', 'string', 'max:120'],
            'duree_semaines' => ['required', 'integer', 'min:1', 'max:104'],
            'date_debut' => ['nullable', 'date'],
            'remuneration' => ['nullable', 'string', 'max:80'],
            'domaine' => ['nullable', 'string', 'max:120'],
            'formation_id' => ['nullable', Rule::exists('formations', 'id')],
            'status' => ['required', Rule::in([Offre::STATUS_DRAFT, Offre::STATUS_PUBLISHED, Offre::STATUS_ARCHIVED])],
        ]);
    }
}
