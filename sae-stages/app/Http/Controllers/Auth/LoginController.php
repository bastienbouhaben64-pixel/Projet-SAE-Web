<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use App\Services\ActivityLogger;
use App\Services\OtpService;
use App\Services\Parametres;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function show(Request $request)
    {
        $role = $request->query('role');
        if (! in_array($role, Utilisateur::ROLES, true)) {
            $role = null;
        }
        return view('auth.login', ['roleSelectionne' => $role]);
    }

    public function store(Request $request, OtpService $otp)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'role' => ['required', \Illuminate\Validation\Rule::in(Utilisateur::ROLES)],
        ]);

        $key = 'login:'.strtolower($data['email']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Trop de tentatives. Réessayez dans une minute.',
            ]);
        }

        $utilisateur = Utilisateur::where('email', $data['email'])->first();
        if (! $utilisateur || ! Hash::check($data['password'], $utilisateur->password)) {
            RateLimiter::hit($key, 60);
            ActivityLogger::log('login.failed', ['email' => $data['email']]);
            throw ValidationException::withMessages([
                'email' => 'Identifiants incorrects.',
            ]);
        }

        if (! $utilisateur->is_active) {
            ActivityLogger::log('login.inactive', ['email' => $data['email']], $utilisateur->id);
            throw ValidationException::withMessages([
                'email' => "Compte non activé. Contactez l'administrateur.",
            ]);
        }

        if ($utilisateur->role !== $data['role']) {
            RateLimiter::hit($key, 60);
            ActivityLogger::log('login.role_mismatch', [
                'email' => $data['email'],
                'expected' => $utilisateur->role,
                'tried' => $data['role'],
            ], $utilisateur->id);
            throw ValidationException::withMessages([
                'role' => "Ce compte n'a pas le rôle « {$data['role']} ». Sélectionnez le bon espace.",
            ]);
        }

        RateLimiter::clear($key);

        // Si l'admin a désactivé l'A2F email, la connexion s'arrête ici.
        if (! Parametres::email2faEnabled()) {
            Auth::login($utilisateur, (bool) $request->boolean('remember'));
            $utilisateur->update(['last_login_at' => now()]);
            $request->session()->regenerate();
            ActivityLogger::log('login.success_without_2fa', [], $utilisateur->id);
            return redirect()->route('tableau_bord');
        }

        // A2F activée : on garde l'utilisateur en attente puis on envoie l'OTP.
        $request->session()->put('pending_2fa_user_id', $utilisateur->id);
        $request->session()->put('pending_2fa_remember', (bool) $request->boolean('remember'));

        $otp->generateAndSend($utilisateur, $request->ip());
        ActivityLogger::log('login.password_ok', [], $utilisateur->id);

        return redirect()->route('otp.afficher');
    }

    public function destroy(Request $request)
    {
        $userId = Auth::id();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        ActivityLogger::log('deconnexion', [], $userId);
        return redirect()->route('connexion');
    }
}
