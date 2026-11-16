<?php

namespace Tests\Feature;

use App\Models\Goal;
use App\Models\User;
use App\Models\Wallet;
use App\Services\GoalService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalServiceAndControllerTest extends TestCase
{
    use RefreshDatabase;

    private GoalService $goalService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->goalService = app(GoalService::class);
    }

    public function test_goal_crud_contribution_and_auto_achieved_status(): void
    {
        $user = User::factory()->create();

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA Tabungan',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 5000000,
            'is_default' => true,
            'is_active' => true,
        ]);

        // Create goal
        $this->actingAs($user)
            ->post(route('goals.store'), [
                'title' => 'Laptop Baru',
                'currency' => 'IDR',
                'target_amount' => 2000000,
                'target_date' => now()->addMonths(4)->toDateString(),
                'icon' => 'laptop_mac',
            ])
            ->assertRedirect(route('goals.index'));

        $goal = Goal::where('user_id', $user->id)->where('title', 'Laptop Baru')->firstOrFail();

        // Update goal
        $this->actingAs($user)
            ->put(route('goals.update', $goal), [
                'title' => 'MacBook Air',
                'currency' => 'IDR',
                'target_amount' => 2000000,
                'target_date' => now()->addMonths(4)->toDateString(),
                'icon' => 'laptop_mac',
            ])
            ->assertRedirect(route('goals.index'));

        $this->assertEquals('MacBook Air', $goal->fresh()->title);

        // First contribution: 500k (25% -> still in_progress)
        $this->actingAs($user)
            ->post(route('goals.contributions.store', $goal), [
                'wallet_id' => $wallet->id,
                'amount' => 500000,
                'contribution_date' => '2026-11-16',
                'notes' => 'Setoran awal',
            ])
            ->assertRedirect(route('goals.index'));

        $this->assertEquals('4500000.0000', $wallet->fresh()->balance);
        $this->assertEquals('500000.00', $goal->fresh()->current_amount);
        $this->assertEquals('in_progress', $goal->fresh()->status);

        $insights = $this->goalService->computeGoalInsights($goal->fresh('contributions'));
        $this->assertEquals(25.0, $insights['percentage']);
        $this->assertEquals(1500000.0, $insights['remaining']);

        // Second contribution: 1.5M (100% -> achieved)
        $this->actingAs($user)
            ->post(route('goals.contributions.store', $goal), [
                'wallet_id' => $wallet->id,
                'amount' => 1500000,
                'contribution_date' => '2026-11-16',
            ])
            ->assertRedirect(route('goals.index'));

        $this->assertEquals('3000000.0000', $wallet->fresh()->balance);
        $this->assertEquals('2000000.00', $goal->fresh()->current_amount);
        $this->assertEquals('achieved', $goal->fresh()->status);

        $response = $this->actingAs($user)->get(route('goals.index'));
        $response->assertOk();
        $response->assertSee('MacBook Air');
        $response->assertSee('Tercapai');
    }

    public function test_contribution_validates_currency_and_authorization(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $usdWallet = Wallet::create([
            'user_id' => $owner->id,
            'name' => 'Wise USD',
            'type' => 'bank',
            'currency' => 'USD',
            'balance' => 500,
            'is_default' => true,
            'is_active' => true,
        ]);

        $goal = $this->goalService->createGoal($owner, [
            'title' => 'Dana Darurat',
            'currency' => 'IDR',
            'target_amount' => 10000000,
        ]);

        $this->actingAs($otherUser)
            ->delete(route('goals.destroy', $goal))
            ->assertForbidden();

        $this->expectException(DomainException::class);
        $this->goalService->contribute($goal, [
            'wallet_id' => $usdWallet->id,
            'amount' => 100,
            'contribution_date' => '2026-11-16',
        ]);
    }
}
