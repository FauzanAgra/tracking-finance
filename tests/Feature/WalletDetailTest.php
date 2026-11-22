<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_wallet_detail_with_metrics_and_transactions(): void
    {
        $user = User::factory()->create();

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA Syariah',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 5000000,
            'is_default' => true,
            'is_active' => true,
        ]);

        $cat = Category::create([
            'user_id' => $user->id,
            'name' => 'Gaji',
            'type' => 'income',
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $cat->id,
            'type' => 'income',
            'amount' => 5000000,
            'transaction_date' => now()->toDateString(),
            'description' => 'Gaji November',
        ]);

        $response = $this->actingAs($user)->get(route('wallets.show', $wallet));

        $response->assertOk();
        $response->assertSee('BCA Syariah');
        $response->assertSee('Gaji November');
        $response->assertSee('5.000.000');
    }

    public function test_user_cannot_view_another_users_wallet(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $walletA = Wallet::create([
            'user_id' => $userA->id,
            'name' => 'Dompet A',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 100000,
            'is_default' => true,
            'is_active' => true,
        ]);

        $this->actingAs($userB)->get(route('wallets.show', $walletA))
            ->assertForbidden();
    }

    public function test_wallet_detail_paginates_transactions(): void
    {
        $user = User::factory()->create();

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'Mandiri',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 1000000,
            'is_default' => true,
            'is_active' => true,
        ]);

        for ($i = 1; $i <= 25; $i++) {
            Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'type' => 'expense',
                'amount' => 10000,
                'transaction_date' => now()->subDays($i)->toDateString(),
                'description' => "Pengeluaran #{$i}",
            ]);
        }

        $response = $this->actingAs($user)->get(route('wallets.show', $wallet));
        $response->assertOk();
        $response->assertSee('Pengeluaran #1');
        $response->assertSee('Hal. 1 / 2');

        $response2 = $this->actingAs($user)->get(route('wallets.show', [$wallet, 'page' => 2]));
        $response2->assertOk();
        $response2->assertSee('Pengeluaran #25');
    }
}
