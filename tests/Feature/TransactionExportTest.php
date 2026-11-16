<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_export_filtered_transactions_to_csv(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA Utama',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 1000000,
            'is_default' => true,
            'is_active' => true,
        ]);

        $otherWallet = Wallet::create([
            'user_id' => $otherUser->id,
            'name' => 'Dompet Orang',
            'type' => 'cash',
            'currency' => 'IDR',
            'balance' => 50000,
        ]);

        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Makanan',
            'type' => 'expense',
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 45000,
            'transaction_date' => '2026-11-16',
            'description' => 'Kopi Susu',
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => 'income',
            'amount' => 250000,
            'transaction_date' => '2026-11-16',
            'description' => 'Refund Proyek',
        ]);

        Transaction::create([
            'user_id' => $otherUser->id,
            'wallet_id' => $otherWallet->id,
            'type' => 'expense',
            'amount' => 99000,
            'transaction_date' => '2026-11-16',
            'description' => 'Rahasia Orang Lain',
        ]);

        $response = $this->actingAs($user)->get(route('transactions.export', ['type' => 'expense']));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Kopi Susu', $csv);
        $this->assertStringNotContainsString('Refund Proyek', $csv);
        $this->assertStringNotContainsString('Rahasia Orang Lain', $csv);
    }
}
