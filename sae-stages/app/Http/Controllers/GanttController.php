<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use Illuminate\Http\Request;

class GanttController extends Controller
{
    public function index(Request $request)
    {
        $u = $request->user();
        $q = Stage::with(['offer', 'student', 'tutor', 'convention'])->actifs();

        if ($u->isEtudiant()) $q->where('student_id', $u->id);
        elseif ($u->isEntreprise()) $q->where('company_id', $u->id);
        elseif ($u->isProfesseur()) $q->where('tutor_id', $u->id);
        elseif (! ($u->isAdmin() || $u->isJury())) abort(403);

        $stages = $q->orderBy('date_debut')->get();

        $min = $stages->min('date_debut');
        $max = $stages->max('date_fin');

        return view('gantt.index', compact('stages', 'min', 'max'));
    }
}
