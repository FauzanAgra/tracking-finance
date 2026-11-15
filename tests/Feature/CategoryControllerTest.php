<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_crud_categories(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('categories.index'))
            ->assertOk();

        $this->actingAs($user)
            ->post(route('categories.store'), [
                'name' => 'Kopi & Snack',
                'type' => 'expense',
                'icon' => 'local_cafe',
                'color' => '#F43F5E',
            ])
            ->assertRedirect(route('categories.index'));

        $category = Category::where('user_id', $user->id)
            ->where('name', 'Kopi & Snack')
            ->firstOrFail();

        $this->actingAs($user)
            ->put(route('categories.update', $category), [
                'name' => 'Makanan & Minuman',
                'type' => 'expense',
                'icon' => 'restaurant',
                'color' => '#F59E0B',
            ])
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Makanan & Minuman',
            'icon' => 'restaurant',
        ]);

        $this->actingAs($user)
            ->delete(route('categories.destroy', $category))
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_with_transactions_cannot_be_deleted(): void
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

        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Transportasi',
            'type' => 'expense',
            'icon' => 'directions_car',
            'color' => '#3B82F6',
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 25000,
            'transaction_date' => now()->toDateString(),
        ]);

        $this->actingAs($user)
            ->delete(route('categories.destroy', $category))
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_user_cannot_modify_or_delete_another_users_category(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $category = Category::create([
            'user_id' => $owner->id,
            'name' => 'Gaji',
            'type' => 'income',
        ]);

        $this->actingAs($otherUser)
            ->put(route('categories.update', $category), [
                'name' => 'Hacked',
                'type' => 'income',
            ])
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->delete(route('categories.destroy', $category))
            ->assertForbidden();
    }
}
