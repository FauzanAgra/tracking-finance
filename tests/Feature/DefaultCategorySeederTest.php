<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\DefaultCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DefaultCategorySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_category_seeder_populates_categories_idempotently(): void
    {
        $user = User::factory()->create();

        $this->seed(DefaultCategorySeeder::class);
        $expectedCount = count(DefaultCategorySeeder::defaultCategories());

        $this->assertCount($expectedCount, $user->categories()->get());
        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'Makanan & Minuman',
            'type' => 'expense',
        ]);
        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'Gaji',
            'type' => 'income',
        ]);
        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'Sedekah / Write-off',
            'type' => 'expense',
        ]);

        // Running the seeder a second time must not duplicate categories
        $this->seed(DefaultCategorySeeder::class);
        $this->assertCount($expectedCount, Category::forUser($user->id)->get());
    }

    public function test_newly_registered_user_receives_default_categories(): void
    {
        $response = $this->post('/register', [
            'name' => 'Agra Developer',
            'email' => 'agra@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'agra@example.com')->firstOrFail();
        $expectedCount = count(DefaultCategorySeeder::defaultCategories());

        $this->assertCount($expectedCount, $user->categories()->get());
    }
}
