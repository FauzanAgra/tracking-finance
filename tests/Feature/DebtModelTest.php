<?php

namespace Tests\Feature;

use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebtModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_debt_has_expected_defaults_and_payments_relationship(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'name' => 'Cash']);

        $debt = Debt::create([
            'user_id' => $user->id,
            'person_name' => 'Jamal',
            'type' => 'receivable',
            'total_amount' => 500000,
            'issue_date' => '2026-11-15',
        ]);

        $debt->refresh();

        $this->assertSame('IDR', $debt->currency);
        $this->assertSame('0.00', $debt->written_off_amount);
        $this->assertSame('unpaid', $debt->status);

        $payment = DebtPayment::create([
            'debt_id' => $debt->id,
            'wallet_id' => $wallet->id,
            'amount' => 200000,
            'payment_date' => '2026-11-15',
            'notes' => 'Bayar cash di warung',
        ]);

        $this->assertCount(1, $debt->payments);
        $this->assertTrue($payment->debt->is($debt));
        $this->assertTrue($payment->wallet->is($wallet));
        $this->assertCount(1, $user->debts);

        $debt->delete();
        $this->assertDatabaseMissing('debt_payments', ['id' => $payment->id]);
    }
}
