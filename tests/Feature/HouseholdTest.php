<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\User;
use App\Models\Wallet;
use App\Services\HouseholdService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HouseholdTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_household(): void
    {
        $response = $this->get(route('household.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_create_household_and_becomes_owner(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('household.store'), [
            'name' => 'Keluarga Jamal & Siti',
        ]);

        $response->assertRedirect(route('household.index'));
        $this->assertDatabaseHas('households', [
            'owner_id' => $user->id,
            'name' => 'Keluarga Jamal & Siti',
        ]);

        $household = Household::where('owner_id', $user->id)->first();
        $this->assertNotNull($household);
        $this->assertStringStartsWith('FAM-', $household->invite_code);

        $this->assertDatabaseHas('household_members', [
            'household_id' => $household->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);

        // Access index page
        $this->actingAs($user)
            ->get(route('household.index'))
            ->assertOk()
            ->assertSee('Keluarga Jamal & Siti')
            ->assertSee('Kepala Keluarga');
    }

    public function test_user_cannot_create_multiple_households(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('household.store'), [
            'name' => 'Keluarga Pertama',
        ]);

        $response = $this->actingAs($user)->post(route('household.store'), [
            'name' => 'Keluarga Kedua',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(1, Household::count());
    }

    public function test_user_can_join_household_with_valid_invite_code(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create(['name' => 'Siti Nurhaliza']);

        $service = app(HouseholdService::class);
        $household = $service->createHousehold($owner, 'Keluarga Bahagia');

        $response = $this->actingAs($member)->post(route('household.join'), [
            'invite_code' => $household->invite_code,
        ]);

        $response->assertRedirect(route('household.index'));
        $this->assertDatabaseHas('household_members', [
            'household_id' => $household->id,
            'user_id' => $member->id,
            'role' => 'member',
        ]);

        $this->actingAs($member)
            ->get(route('household.index'))
            ->assertOk()
            ->assertSee('Keluarga Bahagia')
            ->assertSee('Anggota');
    }

    public function test_user_cannot_join_with_invalid_invite_code(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('household.join'), [
            'invite_code' => 'FAM-INVALID99',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(0, HouseholdMember::count());
    }

    public function test_owner_can_regenerate_invite_code(): void
    {
        $owner = User::factory()->create();
        $household = app(HouseholdService::class)->createHousehold($owner, 'Keluarga Asri');
        $oldCode = $household->invite_code;

        $response = $this->actingAs($owner)->post(route('household.invite-code'));
        $response->assertRedirect();

        $household->refresh();
        $this->assertNotEquals($oldCode, $household->invite_code);
    }

    public function test_owner_can_remove_member(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $service = app(HouseholdService::class);
        $household = $service->createHousehold($owner, 'Keluarga Sejahtera');
        $service->joinHousehold($member, $household->invite_code);

        $this->assertEquals(2, $household->members()->count());

        $response = $this->actingAs($owner)->delete(route('household.remove-member', $member->id));
        $response->assertRedirect();

        $this->assertEquals(1, $household->fresh()->members()->count());
    }

    public function test_member_can_leave_household(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $service = app(HouseholdService::class);
        $household = $service->createHousehold($owner, 'Keluarga Damai');
        $service->joinHousehold($member, $household->invite_code);

        $response = $this->actingAs($member)->post(route('household.leave'));
        $response->assertRedirect(route('household.index'));

        $this->assertDatabaseMissing('household_members', [
            'household_id' => $household->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_owner_can_dissolve_household(): void
    {
        $owner = User::factory()->create();
        $service = app(HouseholdService::class);
        $household = $service->createHousehold($owner, 'Keluarga Sementara');

        $response = $this->actingAs($owner)->delete(route('household.destroy'));
        $response->assertRedirect(route('household.index'));

        $this->assertDatabaseMissing('households', ['id' => $household->id]);
        $this->assertDatabaseMissing('household_members', ['household_id' => $household->id]);
    }

    public function test_user_can_toggle_wallet_share_for_household(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'Tabungan Bersama',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 15000000,
            'is_shared' => false,
        ]);

        $response = $this->actingAs($user)->patch(route('wallets.toggle-share', $wallet));
        $response->assertRedirect();

        $this->assertTrue($wallet->fresh()->is_shared);

        // Toggle back to false
        $this->actingAs($user)->patch(route('wallets.toggle-share', $wallet));
        $this->assertFalse($wallet->fresh()->is_shared);
    }

    public function test_household_summary_computes_shared_wealth(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $service = app(HouseholdService::class);
        $household = $service->createHousehold($owner, 'Keluarga Kaya');
        $service->joinHousehold($member, $household->invite_code);

        Wallet::create([
            'user_id' => $owner->id,
            'name' => 'BCA Owner Shared',
            'type' => 'bank',
            'balance' => 10000000,
            'is_shared' => true,
        ]);

        Wallet::create([
            'user_id' => $owner->id,
            'name' => 'BCA Owner Private',
            'type' => 'bank',
            'balance' => 5000000,
            'is_shared' => false,
        ]);

        Wallet::create([
            'user_id' => $member->id,
            'name' => 'Mandiri Member Shared',
            'type' => 'bank',
            'balance' => 7000000,
            'is_shared' => true,
        ]);

        $summary = $service->getHouseholdSummary($household);

        $this->assertEquals(17000000, $summary['total_shared_wealth']);
        $this->assertEquals(2, $summary['shared_wallets']->count());
    }
}
