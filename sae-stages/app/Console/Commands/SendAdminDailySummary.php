<?php

namespace App\Console\Commands;

use App\Models\Candidature;
use App\Models\DemandeFormation;
use App\Models\Stage;
use App\Models\Utilisateur;
use App\Services\Notify;
use Illuminate\Console\Command;

class SendAdminDailySummary extends Command
{
    protected $signature = 'sae:admin-summary';
    protected $description = "Envoie un résumé quotidien à chaque admin (notifications in-app).";

    public function handle(): int
    {
        $since = now()->subDay();

        $stats = [
            'new_users' => Utilisateur::where('created_at', '>=', $since)->count(),
            'new_applications' => Candidature::where('created_at', '>=', $since)->count(),
            'new_formation_requests' => DemandeFormation::where('created_at', '>=', $since)->where('status', 'pending')->count(),
            'pending_conventions' => Stage::whereHas('convention', fn ($q) => $q->whereNotNull('signed_student_at')
                ->whereNotNull('signed_company_at')->whereNotNull('signed_tutor_at')->whereNull('validated_admin_at'))->count(),
            'stages_to_validate' => Stage::where('status', Stage::STATUS_TERMINE)->count(),
        ];

        $msg = sprintf(
            "%d nouveaux utilisateurs · %d candidatures · %d demandes formation · %d conventions à valider · %d stages à valider",
            $stats['new_users'], $stats['new_applications'], $stats['new_formation_requests'],
            $stats['pending_conventions'], $stats['stages_to_validate']
        );

        $count = 0;
        Utilisateur::where('role', Utilisateur::ROLE_ADMIN)->where('is_active', true)->each(function ($admin) use ($msg, &$count) {
            Notify::send($admin, 'admin.daily_summary', 'Résumé quotidien', $msg, route('admin.traces.index'));
            $count++;
        });

        $this->info("Résumé envoyé à {$count} administrateurs.");
        return self::SUCCESS;
    }
}
