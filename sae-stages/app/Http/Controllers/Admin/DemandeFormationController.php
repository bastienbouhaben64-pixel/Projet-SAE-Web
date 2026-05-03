<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Formation;
use App\Models\DemandeFormation;
use App\Models\ProfilEtudiant;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DemandeFormationController extends Controller
{
    public function index(Request $request)
    {
        $demandes = DemandeFormation::with('user')
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(20);
        return view('admin.demandes_formation.index', compact('demandes'));
    }

    public function decide(DemandeFormation $demandeFormation, Request $request)
    {
        $data = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'reject'])],
            'admin_comment' => ['nullable', 'string', 'max:1000'],
            'code' => ['nullable', 'string', 'max:30'],
        ]);

        DB::transaction(function () use ($demandeFormation, $data, $request) {
            if ($data['decision'] === 'approve') {
                $code = ($data['code'] ?? null) ?: Str::upper(Str::slug(Str::limit($demandeFormation->intitule, 12, '')));
                $i = 1;
                $base = $code;
                while (Formation::where('code', $code)->exists()) {
                    $code = $base.'-'.$i++;
                }
                $formation = Formation::create([
                    'code' => $code,
                    'intitule' => $demandeFormation->intitule,
                    'is_active' => true,
                ]);

                // attach to requesting student if applicable
                ProfilEtudiant::where('user_id', $demandeFormation->user_id)
                    ->whereNull('formation_id')
                    ->update(['formation_id' => $formation->id]);

                $demandeFormation->update([
                    'status' => DemandeFormation::STATUS_APPROVED,
                    'admin_comment' => $data['admin_comment'] ?? null,
                    'handled_by' => $request->user()->id,
                    'handled_at' => now(),
                ]);
            } else {
                $demandeFormation->update([
                    'status' => DemandeFormation::STATUS_REJECTED,
                    'admin_comment' => $data['admin_comment'] ?? null,
                    'handled_by' => $request->user()->id,
                    'handled_at' => now(),
                ]);
            }
        });

        ActivityLogger::log('admin.formation_request.'.$data['decision'], ['id' => $demandeFormation->id]);
        return back()->with('status', 'Demande traitée.');
    }
}
