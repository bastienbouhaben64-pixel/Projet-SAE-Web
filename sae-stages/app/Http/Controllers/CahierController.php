<?php

namespace App\Http\Controllers;

use App\Models\EntreeCahier;
use App\Models\Stage;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class CahierController extends Controller
{
    public function store(Stage $stage, Request $request)
    {
        $u = $request->user();
        // Only the student can write entries
        abort_unless($u->id === $stage->student_id || $u->isAdmin(), 403);
        $data = $request->validate([
            'date' => ['required', 'date'],
            'titre' => ['required', 'string', 'max:200'],
            'contenu' => ['required', 'string', 'max:5000'],
        ]);
        $entry = EntreeCahier::create($data + ['stage_id' => $stage->id]);
        ActivityLogger::log('cahier.added', ['stage_id' => $stage->id, 'entry_id' => $entry->id]);
        return back()->with('status', 'Entrée ajoutée au cahier.');
    }

    public function destroy(EntreeCahier $cahierEntry, Request $request)
    {
        $u = $request->user();
        abort_unless($u->isAdmin() || $cahierEntry->stage->student_id === $u->id, 403);
        $cahierEntry->delete();
        return back()->with('status', 'Entrée supprimée.');
    }
}
