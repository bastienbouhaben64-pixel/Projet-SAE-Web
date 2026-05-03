<?php

namespace Tests\Feature;

use App\Models\Utilisateur;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_loads_with_all_widgets(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = Utilisateur::where('email', 'admin@sae.local')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('tableau_bord'))
            ->assertOk()
            ->assertSee('Tableau de bord administrateur')
            ->assertSee('Pipeline candidatures')
            ->assertSee('Étapes signature convention')
            ->assertSee('Distribution des notes jury')
            ->assertSee('Top lieux d', false)
            ->assertSee('Note moyenne jury')
            ->assertSee('Délai signature')
            ->assertSee('Durée moyenne');
    }
}
