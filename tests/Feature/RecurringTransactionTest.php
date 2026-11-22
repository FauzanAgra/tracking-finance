<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\RecurringTransactionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecurringTransactionTest extends TestCase
{
    use RefreshDatabase;

    private RecurringTransactionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(RecurringTransactionService::class);
    }

    public function test_user_can_create_and_view_recurring_transactions(): void
    {
        $user = User::factory()->create();

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 1000000,
            'is_default' => true,
            'is_active' => true,
        ]);

        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Langganan',
            'type' => 'expense',
        ]);

        $this->actingAs($user)->post(route('recurring.store'), [
            'type' => 'expense',
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'amount' => 54000,
            'frequency' => 'monthly',
            'start_date' => '2026-11-20',
            'description' => 'Spotify Premium',
        ])->assertRedirect(route('recurring.index'));

        $this->assertDatabaseHas('recurring_transactions', [
            'user_id' => $user->id,
            'amount' => 54000,
            'frequency' => 'monthly',
            'description' => 'Spotify Premium',
        ]);

        $response = $this->actingAs($user)->get(route('recurring.index'));
        $response->assertOk();
        $response->assertSee('Spotify Premium');
        $response->assertSee('Bulanan');
    }

    public function test_toggle_and_delete_recurring_transaction(): void
    {
        $user = User::factory()->create();

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 500000,
            'is_default' => true,
            'is_active' => true,
        ]);

        $rec = RecurringTransaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'expense',
            'amount' => 100000,
            'frequency' => 'monthly',
            'start_date' => '2026-11-01',
            'next_run_date' => '2026-11-01',
            'is_active' => true,
        ]);

        // Toggle to inactive
        $this->actingAs($user)->patch(route('recurring.toggle', $rec))->assertRedirect(route('recurring.index'));
        $this->assertFalse($rec->fresh()->is_active);

        // Delete
        $this->actingAs($user)->delete(route('recurring.destroy', $rec))->assertRedirect(route('recurring.index'));
        $this->assertDatabaseMissing('recurring_transactions', ['id' => $rec->id]);
    }

    public function test_process_due_creates_transactions_and_advances_next_date(): void
    {
        $user = User::factory()->create();

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 2000000,
            'is_default' => true,
            'is_active' => true,
        ]);

        $cat = Category::create([
            'user_id' => $user->id,
            'name' => 'Kos',
            'type' => 'expense',
        ]);

        $rec = RecurringTransaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $cat->id,
            'type' => 'expense',
            'amount' => 800000,
            'frequency' => 'monthly',
            'start_date' => '2026-11-01',
            'next_run_date' => '2026-11-01',
            'is_active' => true,
            'description' => 'Bayar Kos',
        ]);

        $result = $this->service->processDue(Carbon::parse('2026-11-22'));

        $this->assertEquals(1, $result['processed']);
        $this->assertEquals(0, $result['failed']);

        // Check real transaction created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 800000,
            'type' => 'expense',
        ]);

        // Wallet balance debited
        $this->assertEquals('1200000.0000', $wallet->fresh()->balance);

        // Recurring next_run_date advanced by 1 month to 2026-12-01
        $this->assertEquals('2026-12-01', $rec->fresh()->next_run_date->format('Y-m-d'));
        $this->assertEquals('2026-11-01', $rec->fresh()->last_run_date->format('Y-m-d'));
    }

    public function test_process_command_runs_successfully(): void
    {
        $this->artisan('finance:process-recurring')
            ->expectsOutputToContain('Hasil:')
            ->assertSuccessful();
    }
}
