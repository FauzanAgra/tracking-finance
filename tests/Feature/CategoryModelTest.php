<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_has_expected_default_values(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Makanan & Minuman',
            'type' => 'expense',
        ]);

        $category->refresh();

        $this->assertSame('tag', $category->icon);
        $this->assertSame('#10B981', $category->color);
    }

    public function test_user_has_many_categories_and_scopes_work(): void
    {
        $user = User::factory()->create();

        $income = Category::factory()->create([
            'user_id' => $user->id,
            'name' => 'Gaji',
            'type' => 'income',
        ]);

        $expense = Category::factory()->create([
            'user_id' => $user->id,
            'name' => 'Transportasi',
            'type' => 'expense',
        ]);

        $this->assertCount(2, $user->categories);
        $this->assertTrue($income->user->is($user));
        $this->assertCount(1, Category::forUser($user->id)->ofType('income')->get());
        $this->assertCount(1, Category::forUser($user->id)->ofType('expense')->get());

        $user->delete();

        $this->assertDatabaseMissing('categories', ['id' => $income->id]);
        $this->assertDatabaseMissing('categories', ['id' => $expense->id]);
    }
}
