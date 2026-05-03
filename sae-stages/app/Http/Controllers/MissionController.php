<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use App\Models\Stage;
use App\Services\ActivityLogger;
use App\Services\Notify;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MissionController extends Controller
{
    public function store(Stage $stage, Request $request)
    {
        $u = $request->user();
        // Only the company (or admin) can attribute missions
        abort_unless($u->isAdmin() || $u->id === $stage->company_id, 403);
        $data = $request->validate([
            'titre' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'due_date' => ['nullable', 'date'],
        ]);
        $m = Mission::create($data + ['stage_id' => $stage->id, 'status' => Mission::STATUS_TODO]);
        ActivityLogger::log('mission.created', ['mission_id' => $m->id, 'stage_id' => $stage->id]);
        Notify::send($stage->student_id, 'mission.assigned',
            "Nouvelle mission : {$m->titre}", $m->description, route('stages.afficher', $stage));
        return back()->with('status', 'Mission attribuée.');
    }

    public function update(Mission $mission, Request $request)
    {
        $u = $request->user();
        $stage = $mission->stage;
        $canFullEdit = $u->isAdmin() || $u->id === $stage->company_id;
        $canStatusOnly = $u->id === $stage->student_id;
        abort_unless($canFullEdit || $canStatusOnly, 403);

        $rules = ['status' => ['required', Rule::in(Mission::STATUSES)]];
        if ($canFullEdit) {
            $rules += [
                'titre' => ['required', 'string', 'max:200'],
                'description' => ['nullable', 'string', 'max:2000'],
                'due_date' => ['nullable', 'date'],
            ];
        }
        $data = $request->validate($rules);
        $mission->update($data);
        ActivityLogger::log('mission.updated', ['mission_id' => $mission->id, 'changes' => $data]);
        return back()->with('status', 'Mission mise à jour.');
    }

    public function destroy(Mission $mission, Request $request)
    {
        $u = $request->user();
        abort_unless($u->isAdmin() || $u->id === $mission->stage->company_id, 403);
        $mission->delete();
        return back()->with('status', 'Mission supprimée.');
    }
}
