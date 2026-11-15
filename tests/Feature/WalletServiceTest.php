<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletServiceTest extends TestCase
{
    use RefreshDatabase;

    private WalletService $walletService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->walletService = app(WalletService::class);
    }

    public function test_first_wallet_is_automatically_set_as_default_and_second_default_unsets_previous(): void
    {
        $user = User::factory()->create();

        $first = $this->walletService->createWallet($user, [
            'name' => 'BCA Utama',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 1000000,
            'is_default' => false,
        ]);

        $this->assertTrue((bool) $first->is_default);

        $second = $this->walletService->createWallet($user, [
            'name' => 'GoPay',
            'type' => 'e_wallet',
            'currency' => 'IDR',
            'balance' => 250000,
            'is_default' => true,
        ]);

        $this->assertTrue((bool) $second->is_default);
        $this->assertFalse((bool) $first->fresh()->is_default);
    }

    public function test_credit_and_debit_update_balance_and_insufficient_balance_throws_exception(): void
    {
        $user = User::factory()->create();

        $wallet = $this->walletService->createWallet($user, [
            'name' => 'Mandiri',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 500000,
        ]);

        $this->walletService->credit($wallet, 150000);
        $this->assertEquals('650000.0000', $wallet->fresh()->balance);

        $this->walletService->debit($wallet, 200000);
        $this->assertEquals('450000.0000', $wallet->fresh()->balance);

        $this->expectException(DomainException::class);

        try {
            $this->walletService->debit($wallet, 500000);
        } finally {
            $this->assertEquals('450000.0000', $wallet->fresh()->balance);
        }
    }

    public function test_transfer_same_currency_updates_both_wallets_and_validates_currency_and_balance(): void
    {
        $user = User::factory()->create();

        $from = $this->walletService->createWallet($user, [
            'name' => 'BCA',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 1000000,
        ]);

        $to = $this->walletService->createWallet($user, [
            'name' => 'OVO',
            'type' => 'e_wallet',
            'currency' => 'IDR',
            'balance' => 100000,
        ]);

        $this->walletService->transferSameCurrency($from, $to, 300000, 2500);

        $this->assertEquals('697500.0000', $from->fresh()->balance);
        $this->assertEquals('400000.0000', $to->fresh()->balance);

        $usdWallet = $this->walletService->createWallet($user, [
            'name' => 'USD Vault',
            'type' => 'bank',
            'currency' => 'USD',
            'balance' => 100,
        ]);

        $this->expectException(DomainException::class);
        $this->walletService->transferSameCurrency($from, $usdWallet, 50000);
    }

    public function test_transfer_currency_exchange_returns_rate_and_updates_both_balances(): void
    {
        $user = User::factory()->create();

        $idrWallet = $this->walletService->createWallet($user, [
            'name' => 'BCA IDR',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 20000000,
        ]);

        $usdWallet = $this->walletService->createWallet($user, [
            'name' => 'Wise USD',
            'type' => 'bank',
            'currency' => 'USD',
            'balance' => 0,
        ]);

        $rate = $this->walletService->transferCurrencyExchange($idrWallet, $usdWallet, 16000000, 1000, 25000);

        $this->assertEquals(16000.0, $rate);
        $this->assertEquals('3975000.0000', $idrWallet->fresh()->balance);
        $this->assertEquals('1000.0000', $usdWallet->fresh()->balance);
    }

    public function test_delete_wallet_throws_domain_exception_when_wallet_has_transactions(): void
    {
        $user = User::factory()->create();

        $wallet = $this->walletService->createWallet($user, [
            'name' => 'Dompet Aktif',
            'type' => 'cash',
            'currency' => 'IDR',
            'balance' => 500000,
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'expense',
            'amount' => 25000,
            'description' => 'Makan siang',
            'transaction_date' => now()->toDateString(),
        ]);

        $this->expectException(DomainException::class);
        $this->walletService->deleteWallet($wallet);
    }
}
