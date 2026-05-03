<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Formation;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FormationController extends Controller
{
    public function index()
    {
        $formations = Formation::orderBy('intitule')->paginate(30);
        return view('admin.formations.index', compact('formations'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('formations', 'code')],
            'intitule' => ['required', 'string', 'max:200'],
        ]);
        $data['is_active'] = true;
        Formation::create($data);
        ActivityLogger::log('admin.formation.created', $data);
        return back()->with('status', 'Formation ajoutée.');
    }

    public function update(Formation $formation, Request $request)
    {
        $data = $request->validate([
            'intitule' => ['required', 'string', 'max:200'],
            'is_active' => ['required', 'boolean'],
        ]);
        $formation->update($data);
        ActivityLogger::log('admin.formation.updated', ['id' => $formation->id]);
        return back()->with('status', 'Formation mise à jour.');
    }

    public function destroy(Formation $formation)
    {
        $formation->delete();
        ActivityLogger::log('admin.formation.deleted', ['id' => $formation->id]);
        return back()->with('status', 'Formation supprimée.');
    }
}
