<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trace;
use Illuminate\Http\Request;

class TraceController extends Controller
{
    public function index(Request $request)
    {
        $traces = Trace::with('user')
            ->when($request->action, fn ($q, $v) => $q->where('action', 'like', "%$v%"))
            ->when($request->user_id, fn ($q, $v) => $q->where('user_id', $v))
            ->when($request->from, fn ($q, $v) => $q->where('created_at', '>=', $v))
            ->when($request->to, fn ($q, $v) => $q->where('created_at', '<=', $v))
            ->orderByDesc('created_at')
            ->paginate(50)
            ->withQueryString();

        return view('admin.traces.index', compact('traces'));
    }
}
