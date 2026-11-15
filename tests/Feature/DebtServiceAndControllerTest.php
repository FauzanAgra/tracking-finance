<?php

namespace Tests\Feature;

use App\Models\Debt;
use App\Models\User;
use App\Models\Wallet;
use App\Services\DebtService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebtServiceAndControllerTest extends TestCase
{
    use RefreshDatabase;

    private DebtService $debtService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->debtService = app(DebtService::class);
    }

    public function test_jamal_receivable_partial_installments_across_wallets_and_write_off(): void
    {
        $user = User::factory()->create();

        $cash = Wallet::create([
            'user_id' => $user->id,
            'name' => 'Dompet Cash',
            'type' => 'cash',
            'currency' => 'IDR',
            'balance' => 100000,
            'is_default' => true,
            'is_active' => true,
        ]);

        $bca = Wallet::create([
            'user_id' => $user->id,
            'name' => 'Bank BCA',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 500000,
            'is_default' => false,
            'is_active' => true,
        ]);

        // Create Jamal receivable (400k)
        $this->actingAs($user)
            ->post(route('debts.store'), [
                'person_name' => 'Jamal',
                'type' => 'receivable',
                'currency' => 'IDR',
                'total_amount' => 400000,
                'issue_date' => '2026-11-06',
                'due_date' => '2026-11-20',
                'notes' => 'Pinjam dana darurat',
            ])
            ->assertRedirect(route('debts.index'));

        $debt = Debt::where('user_id', $user->id)->where('person_name', 'Jamal')->firstOrFail();
        $this->assertEquals('unpaid', $debt->status);

        // Friday: 200k Cash
        $this->actingAs($user)
            ->post(route('debts.payments.store', $debt), [
                'wallet_id' => $cash->id,
                'amount' => 200000,
                'payment_date' => '2026-11-08',
                'notes' => 'Bayar cash di warung',
            ])
            ->assertRedirect(route('debts.index'));

        $this->assertEquals('300000.0000', $cash->fresh()->balance);
        $this->assertEquals('partially_paid', $debt->fresh()->status);

        // Saturday: 100k BCA
        $this->actingAs($user)
            ->post(route('debts.payments.store', $debt), [
                'wallet_id' => $bca->id,
                'amount' => 100000,
                'payment_date' => '2026-11-09',
            ])
            ->assertRedirect(route('debts.index'));

        $this->assertEquals('600000.0000', $bca->fresh()->balance);
        $this->assertEquals('partially_paid', $debt->fresh()->status);

        // Write off remaining 100k cleanly without mutating wallets
        $this->actingAs($user)
            ->patch(route('debts.write-off', $debt), [
                'writeoff_notes' => 'Dianggap impas',
            ])
            ->assertRedirect(route('debts.index'));

        $debt->refresh();
        $this->assertEquals('written_off', $debt->status);
        $this->assertEquals('100000.00', $debt->written_off_amount);
        $this->assertEquals('300000.0000', $cash->fresh()->balance);
        $this->assertEquals('600000.0000', $bca->fresh()->balance);
    }

    public function test_payable_full_payment_debits_wallet_and_marks_status_paid(): void
    {
        $user = User::factory()->create();

        $bca = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 1000000,
            'is_default' => true,
            'is_active' => true,
        ]);

        $debt = $this->debtService->createDebt($user, [
            'person_name' => 'Budi',
            'type' => 'payable',
            'currency' => 'IDR',
            'total_amount' => 250000,
            'issue_date' => '2026-11-10',
        ]);

        $this->debtService->recordPayment($debt, [
            'wallet_id' => $bca->id,
            'amount' => 250000,
            'payment_date' => '2026-11-15',
        ]);

        $this->assertEquals('750000.0000', $bca->fresh()->balance);
        $this->assertEquals('paid', $debt->fresh()->status);
    }

    public function test_overpayment_throws_domain_exception_and_other_user_gets_403(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $wallet = Wallet::create([
            'user_id' => $owner->id,
            'name' => 'BCA',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 500000,
            'is_default' => true,
            'is_active' => true,
        ]);

        $debt = $this->debtService->createDebt($owner, [
            'person_name' => 'Andi',
            'type' => 'receivable',
            'currency' => 'IDR',
            'total_amount' => 100000,
            'issue_date' => '2026-11-10',
        ]);

        $this->actingAs($otherUser)
            ->patch(route('debts.write-off', $debt))
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->delete(route('debts.destroy', $debt))
            ->assertForbidden();

        $this->expectException(DomainException::class);
        $this->debtService->recordPayment($debt, [
            'wallet_id' => $wallet->id,
            'amount' => 150000,
            'payment_date' => '2026-11-15',
        ]);
    }
}
