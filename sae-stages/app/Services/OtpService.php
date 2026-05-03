<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\CodeOtp;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OtpService
{
    public const TTL_MINUTES = 10;

    public function generateAndSend( Utilisateur $utilisateur, ?string $ip = null): CodeOtp
    {
        // invalidate previous codes
        $utilisateur->otpCodes()->whereNull('used_at')->update(['used_at' => now()]);

        $plain = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $otp = CodeOtp::create([
            'user_id' => $utilisateur->id,
            'code_hash' => Hash::make($plain),
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
            'ip' => $ip,
        ]);

        try {
            Mail::to($utilisateur->email)->send(new OtpMail($plain, self::TTL_MINUTES));
        } catch (Throwable $e) {
            Log::warning('OTP email failed', [
                'user_id' => $utilisateur->id,
                'email' => $utilisateur->email,
                'error' => $e->getMessage(),
            ]);
        }

        ActivityLogger::log('otp.sent', ['otp_id' => $otp->id], $utilisateur->id);

        return $otp;
    }

    public function verify( Utilisateur $utilisateur, string $code): bool
    {
        $otp = $utilisateur->otpCodes()
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $otp || ! Hash::check($code, $otp->code_hash)) {
            ActivityLogger::log('otp.failed', [], $utilisateur->id);
            return false;
        }

        $otp->update(['used_at' => now()]);
        ActivityLogger::log('otp.verified', ['otp_id' => $otp->id], $utilisateur->id);
        return true;
    }
}
