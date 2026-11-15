<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_wallets_page(): void
    {
        $user = User::factory()->create();

        Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA Utama',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 1500000,
            'color' => '#3B82F6',
            'icon' => 'account_balance',
            'is_default' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('wallets.index'));

        $response->assertOk();
        $response->assertSee('BCA Utama');
        $response->assertDontSee('modalMode: "create"', false);
        $response->assertSee("modalMode: 'create'", false);
    }

    public function test_authenticated_user_can_create_update_set_default_and_delete_wallet(): void
    {
        $user = User::factory()->create();

        // Create first wallet
        $createFirst = $this->actingAs($user)->post(route('wallets.store'), [
            'name' => 'BCA Payroll',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 5000000,
            'account_number' => '1234567890',
            'color' => '#3B82F6',
            'is_default' => false,
        ]);

        $createFirst->assertRedirect(route('wallets.index'));
        $firstWallet = Wallet::where('user_id', $user->id)->where('name', 'BCA Payroll')->firstOrFail();
        $this->assertTrue((bool) $firstWallet->is_default);

        // Create second wallet
        $createSecond = $this->actingAs($user)->post(route('wallets.store'), [
            'name' => 'GoPay Harian',
            'type' => 'e_wallet',
            'currency' => 'IDR',
            'balance' => 250000,
            'color' => '#10B981',
            'is_default' => false,
        ]);

        $createSecond->assertRedirect(route('wallets.index'));
        $secondWallet = Wallet::where('user_id', $user->id)->where('name', 'GoPay Harian')->firstOrFail();
        $this->assertFalse((bool) $secondWallet->is_default);

        // Update second wallet
        $updateResponse = $this->actingAs($user)->put(route('wallets.update', $secondWallet), [
            'name' => 'GoPay Plus',
            'type' => 'e_wallet',
            'currency' => 'IDR',
            'balance' => 300000,
            'color' => '#10B981',
            'is_default' => false,
        ]);

        $updateResponse->assertRedirect(route('wallets.index'));
        $this->assertDatabaseHas('wallets', [
            'id' => $secondWallet->id,
            'name' => 'GoPay Plus',
            'balance' => 300000,
        ]);

        // Set second wallet as default
        $defaultResponse = $this->actingAs($user)->patch(route('wallets.set-default', $secondWallet));
        $defaultResponse->assertRedirect(route('wallets.index'));
        $this->assertTrue((bool) $secondWallet->fresh()->is_default);
        $this->assertFalse((bool) $firstWallet->fresh()->is_default);

        // Delete unused first wallet
        $deleteResponse = $this->actingAs($user)->delete(route('wallets.destroy', $firstWallet));
        $deleteResponse->assertRedirect(route('wallets.index'));
        $this->assertDatabaseMissing('wallets', ['id' => $firstWallet->id]);
    }

    public function test_user_cannot_modify_or_delete_another_users_wallet(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $wallet = Wallet::create([
            'user_id' => $owner->id,
            'name' => 'Dompet Rahasia',
            'type' => 'cash',
            'currency' => 'IDR',
            'balance' => 100000,
            'color' => '#3B82F6',
            'icon' => 'payments',
            'is_default' => true,
            'is_active' => true,
        ]);

        $this->actingAs($otherUser)
            ->put(route('wallets.update', $wallet), [
                'name' => 'Hacked',
                'type' => 'cash',
                'currency' => 'IDR',
                'balance' => 0,
            ])
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->patch(route('wallets.set-default', $wallet))
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->delete(route('wallets.destroy', $wallet))
            ->assertForbidden();

        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'name' => 'Dompet Rahasia',
        ]);
    }

    public function test_wallet_creation_validates_type_currency_and_balance(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('wallets.store'), [
            'name' => 'Dompet Invalid',
            'type' => 'crypto_invalid',
            'currency' => 'EUR',
            'balance' => -5000,
        ]);

        $response->assertSessionHasErrors(['type', 'currency', 'balance']);
    }
}
