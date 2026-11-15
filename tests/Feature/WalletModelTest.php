<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_wallet_has_expected_default_values(): void
    {
        $user = User::factory()->create();

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA Utama',
            'type' => 'bank',
        ]);

        $wallet->refresh();

        $this->assertSame('IDR', $wallet->currency);
        $this->assertSame('0.0000', $wallet->balance);
        $this->assertSame('#3B82F6', $wallet->color);
        $this->assertSame('wallet', $wallet->icon);
        $this->assertFalse($wallet->is_default);
        $this->assertTrue($wallet->is_active);
    }

    public function test_user_has_many_wallets_and_cascades_on_delete(): void
    {
        $user = User::factory()->create();

        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'name' => 'Binance USDT',
            'type' => 'investment_cash',
            'currency' => 'USDT',
            'balance' => 150.25,
            'is_default' => true,
        ]);

        $this->assertCount(1, $user->wallets);
        $this->assertTrue($wallet->user->is($user));
        $this->assertCount(1, Wallet::forUser($user->id)->active()->get());

        $user->delete();

        $this->assertDatabaseMissing('wallets', ['id' => $wallet->id]);
    }
}
