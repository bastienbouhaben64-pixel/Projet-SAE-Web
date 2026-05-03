<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /** Formulaire de demande (saisie email). */
    public function showRequest()
    {
        return view('auth.mot_de_passe.demander');
    }

    /** Traite la demande : envoie le lien si l'email existe. */
    public function sendLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        ActivityLogger::log('password.reset_requested', ['email' => $request->email, 'status' => $status]);

        // Réponse uniforme pour ne pas révéler l'existence d'un compte
        return back()->with('status', "Si un compte existe pour cette adresse, un email de réinitialisation a été envoyé.");
    }

    /** Formulaire de saisie du nouveau mot de passe (lien depuis l'email). */
    public function showReset(Request $request, string $token)
    {
        return view('auth.mot_de_passe.reinitialiser', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /** Met à jour le mot de passe à partir du token. */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
                event(new PasswordReset($user));
            }
        );

        ActivityLogger::log('password.reset_attempt', ['email' => $request->email, 'status' => $status]);

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('connexion')->with('status', 'Mot de passe réinitialisé. Vous pouvez vous connecter.')
            : back()->withErrors(['email' => __($status)]);
    }
}
