<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionExportPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_export_pdf(): void
    {
        $response = $this->get(route('transactions.export.pdf'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_export_pdf_statement(): void
    {
        $user = User::factory()->create(['name' => 'Budi Santoso', 'base_currency' => 'IDR']);

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA Utama',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 5000000,
            'is_default' => true,
            'is_active' => true,
        ]);

        $categoryExpense = Category::create([
            'user_id' => $user->id,
            'name' => 'Makanan & Minuman',
            'type' => 'expense',
            'color' => '#ef4444',
        ]);

        $categoryIncome = Category::create([
            'user_id' => $user->id,
            'name' => 'Gaji Pokok',
            'type' => 'income',
            'color' => '#10b981',
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $categoryExpense->id,
            'type' => 'expense',
            'amount' => 75000,
            'transaction_date' => '2026-11-20',
            'description' => 'Makan Siang Resto',
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $categoryIncome->id,
            'type' => 'income',
            'amount' => 10000000,
            'transaction_date' => '2026-11-19',
            'description' => 'Transfer Gaji November',
        ]);

        $response = $this->actingAs($user)->get(route('transactions.export.pdf'));

        $response->assertOk();
        $response->assertViewIs('transactions.pdf');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Laporan Arus Kas & Transaksi Keuangan', false);
        $response->assertSee('Makan Siang Resto');
        $response->assertSee('Transfer Gaji November');
        $response->assertSee('Makanan &amp; Minuman', false);
        $response->assertSee('75.000');
        $response->assertSee('10.000.000');
    }

    public function test_export_pdf_respects_filter_and_does_not_leak_other_user_transactions(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'Dompet User',
            'type' => 'cash',
            'currency' => 'IDR',
            'balance' => 500000,
        ]);

        $otherWallet = Wallet::create([
            'user_id' => $otherUser->id,
            'name' => 'Dompet Orang Lain',
            'type' => 'cash',
            'currency' => 'IDR',
            'balance' => 999999,
        ]);

        $txn1 = Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'expense',
            'amount' => 20000,
            'transaction_date' => '2026-11-10',
            'description' => 'Pengeluaran Target Filter',
        ]);

        $txn2 = Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'expense',
            'amount' => 50000,
            'transaction_date' => '2026-10-01',
            'description' => 'Pengeluaran Luar Filter',
        ]);

        $otherTxn = Transaction::create([
            'user_id' => $otherUser->id,
            'wallet_id' => $otherWallet->id,
            'type' => 'expense',
            'amount' => 888888,
            'transaction_date' => '2026-11-10',
            'description' => 'Rahasia Orang Lain',
        ]);

        $response = $this->actingAs($user)->get(route('transactions.export.pdf', [
            'date_from' => '2026-11-01',
            'date_to' => '2026-11-30',
        ]));

        $response->assertOk();
        $response->assertSee('Pengeluaran Target Filter');
        $response->assertDontSee('Pengeluaran Luar Filter');
        $response->assertDontSee('Rahasia Orang Lain');
    }
}
