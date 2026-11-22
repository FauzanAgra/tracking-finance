<?php

namespace Tests\Feature;

use App\Models\Debt;
use App\Models\NetWorthSnapshot;
use App\Models\User;
use App\Models\Wallet;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NetWorthSnapshotTest extends TestCase
{
    use RefreshDatabase;

    private DashboardService $dashboardService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dashboardService = app(DashboardService::class);
    }

    public function test_dashboard_service_takes_snapshot_and_stores_breakdown(): void
    {
        $user = User::factory()->create();

        Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 2000000,
            'is_default' => true,
            'is_active' => true,
        ]);

        Debt::create([
            'user_id' => $user->id,
            'person_name' => 'Budi',
            'type' => 'receivable',
            'currency' => 'IDR',
            'total_amount' => 500000,
            'status' => 'unpaid',
            'issue_date' => '2026-11-01',
        ]);

        $snapshot = $this->dashboardService->takeSnapshot($user, Carbon::parse('2026-11-22'));

        $this->assertDatabaseHas('net_worth_snapshots', [
            'user_id' => $user->id,
        ]);

        $this->assertEquals('2026-11-22', $snapshot->snapshot_date->format('Y-m-d'));
        $this->assertEquals(2500000.0, $snapshot->net_worth_idr);
        $this->assertEquals(2000000.0, $snapshot->cash_idr);
        $this->assertEquals(500000.0, $snapshot->receivable_idr);
    }

    public function test_snapshot_command_executes_for_users(): void
    {
        $user1 = User::factory()->create(['name' => 'User Satu']);
        $user2 = User::factory()->create(['name' => 'User Dua']);

        $this->artisan('finance:snapshot-net-worth')
            ->expectsOutputToContain('User Satu')
            ->expectsOutputToContain('User Dua')
            ->assertSuccessful();

        $this->assertDatabaseCount('net_worth_snapshots', 2);
    }

    public function test_dashboard_view_contains_net_worth_history_chart(): void
    {
        $user = User::factory()->create();

        Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 1000000,
            'is_default' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('net_worth_history');
        $response->assertSee('netWorthTrendChart');
        $response->assertSee('Tren Kekayaan Bersih');
    }
}
