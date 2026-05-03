<?php

namespace App\Http\Controllers;

use App\Models\Formation;
use App\Models\ProfilEntreprise;
use App\Models\ProfilEtudiant;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfilController extends Controller
{
    /** Affiche le profil correspondant au rôle. */
    public function show(Request $request)
    {
        $u = $request->user();

        if ($u->isEtudiant()) {
            $profil = ProfilEtudiant::firstOrCreate(['user_id' => $u->id]);
            $profil->load('formation');
            $formations = Formation::where('is_active', true)->orderBy('intitule')->get();
            return view('profil.etudiant', compact('profil', 'formations'));
        }

        if ($u->isEntreprise()) {
            $profil = ProfilEntreprise::firstOrNew(['user_id' => $u->id]);
            return view('profil.entreprise', compact('profil'));
        }

        if ($u->isProfesseur() || $u->isJury()) {
            return view('profil.personnel', ['profilUser' => $u]);
        }

        // admin : profil minimal
        return view('profil.compte');
    }

    /** Met à jour les informations professionnelles du prof / jury. */
    public function updatePersonnel(Request $request)
    {
        $u = $request->user();
        abort_unless($u->isProfesseur() || $u->isJury(), 403);

        $data = $request->validate([
            'telephone' => ['nullable', 'string', 'max:30'],
            'specialites' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1500'],
            'disponible' => ['nullable', 'boolean'],
        ]);
        $data['disponible'] = (bool) ($data['disponible'] ?? false);

        $u->fill($data)->save();
        ActivityLogger::log('profil.personnel_updated');
        return back()->with('status', 'Profil mis à jour.');
    }

    /** Met à jour les informations du compte (commun à tous les rôles). */
    public function updateAccount(Request $request)
    {
        $u = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180', \Illuminate\Validation\Rule::unique('utilisateurs', 'email')->ignore($u->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $u->name = $data['name'];
        $u->email = $data['email'];
        if (! empty($data['password'])) {
            $u->password = bcrypt($data['password']);
        }
        $u->save();

        ActivityLogger::log('profil.account_updated');
        return back()->with('status', 'Compte mis à jour.');
    }

    /** Met à jour le profil étudiant. */
    public function updateEtudiant(Request $request)
    {
        $u = $request->user();
        abort_unless($u->isEtudiant(), 403);

        $data = $request->validate([
            'formation_id' => ['nullable', 'exists:formations,id'],
            'promo' => ['nullable', 'string', 'max:20'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'cv' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $profil = ProfilEtudiant::firstOrCreate(['user_id' => $u->id]);
        $profil->formation_id = $data['formation_id'] ?? null;
        $profil->promo = $data['promo'] ?? null;
        $profil->telephone = $data['telephone'] ?? null;

        if ($request->hasFile('cv')) {
            if ($profil->cv_path && Storage::disk('public')->exists($profil->cv_path)) {
                Storage::disk('public')->delete($profil->cv_path);
            }
            $profil->cv_path = $request->file('cv')->store("cv/{$u->id}", 'public');
        }

        $profil->save();
        ActivityLogger::log('profil.etudiant_updated');
        return back()->with('status', 'Profil étudiant enregistré.');
    }

    /** Supprime le CV PDF. */
    public function deleteCv(Request $request)
    {
        $u = $request->user();
        abort_unless($u->isEtudiant(), 403);
        $profil = ProfilEtudiant::where('user_id', $u->id)->firstOrFail();
        if ($profil->cv_path && Storage::disk('public')->exists($profil->cv_path)) {
            Storage::disk('public')->delete($profil->cv_path);
        }
        $profil->update(['cv_path' => null]);
        return back()->with('status', 'CV supprimé.');
    }

    /** Met à jour le profil entreprise. */
    public function updateEntreprise(Request $request)
    {
        $u = $request->user();
        abort_unless($u->isEntreprise(), 403);

        $data = $request->validate([
            'raison_sociale' => ['required', 'string', 'max:180'],
            'siret' => ['nullable', 'string', 'max:20'],
            'secteur' => ['nullable', 'string', 'max:120'],
            'site_web' => ['nullable', 'url', 'max:200'],
            'adresse' => ['nullable', 'string', 'max:300'],
        ]);

        $profil = ProfilEntreprise::updateOrCreate(['user_id' => $u->id], $data);

        ActivityLogger::log('profil.entreprise_updated');
        return back()->with('status', 'Profil entreprise enregistré.');
    }
}
