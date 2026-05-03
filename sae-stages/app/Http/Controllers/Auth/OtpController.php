<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use App\Services\ActivityLogger;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class OtpController extends Controller
{
    public function show(Request $request)
    {
        if (! $request->session()->has('pending_2fa_user_id')) {
            return redirect()->route('connexion');
        }
        return view('auth.otp');
    }

    public function verify(Request $request, OtpService $otp)
    {
        $request->merge([
            'code' => preg_replace('/\D+/', '', (string) $request->input('code')),
        ]);

        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $userId = $request->session()->get('pending_2fa_user_id');
        if (! $userId) {
            return redirect()->route('connexion');
        }

        $utilisateur = Utilisateur::findOrFail($userId);

        if (! $otp->verify($utilisateur, $data['code'])) {
            throw ValidationException::withMessages([
                'code' => 'Code invalide ou expiré.',
            ]);
        }

        $remember = (bool) $request->session()->pull('pending_2fa_remember', false);
        $request->session()->forget('pending_2fa_user_id');

        Auth::login($utilisateur, $remember);
        $utilisateur->update(['last_login_at' => now()]);
        $request->session()->regenerate();

        ActivityLogger::log('login.success', [], $utilisateur->id);

        return redirect()->route('tableau_bord');
    }

    public function resend(Request $request, OtpService $otp)
    {
        $userId = $request->session()->get('pending_2fa_user_id');
        if (! $userId) {
            return redirect()->route('connexion');
        }
        $utilisateur = Utilisateur::findOrFail($userId);
        $otp->generateAndSend($utilisateur, $request->ip());
        return back()->with('status', 'Un nouveau code a été envoyé.');
    }
}
