<?php

namespace App\Http\Controllers;

use App\Models\DemandeFormation;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class DemandeFormationController extends Controller
{
    public function create()
    {
        return view('demandes_formation.creer');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'intitule' => ['required', 'string', 'max:200'],
            'justification' => ['nullable', 'string', 'max:2000'],
        ]);
        $data['user_id'] = $request->user()->id;

        $fr = DemandeFormation::create($data);
        ActivityLogger::log('formation_request.created', ['id' => $fr->id]);

        return redirect()->route('tableau_bord')->with('status', 'Demande envoyée. L\'administrateur va l\'examiner.');
    }

    public function myIndex(Request $request)
    {
        $demandes = $request->user()->load([])
            ? DemandeFormation::where('user_id', $request->user()->id)->latest()->paginate(20)
            : null;
        return view('demandes_formation.miennes', compact('demandes'));
    }
}
