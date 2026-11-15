<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class DefaultCategorySeeder extends Seeder
{
    /**
     * Default categories seeded for each user.
     *
     * @return array<int, array{name: string, type: string, icon: string, color: string}>
     */
    public static function defaultCategories(): array
    {
        return [
            // Expense categories
            ['name' => 'Makanan & Minuman', 'type' => 'expense', 'icon' => 'restaurant', 'color' => '#F59E0B'],
            ['name' => 'Transportasi', 'type' => 'expense', 'icon' => 'directions_car', 'color' => '#3B82F6'],
            ['name' => 'Belanja & Kebutuhan', 'type' => 'expense', 'icon' => 'shopping_bag', 'color' => '#EC4899'],
            ['name' => 'Tagihan & Utilitas', 'type' => 'expense', 'icon' => 'receipt_long', 'color' => '#8B5CF6'],
            ['name' => 'Kesehatan', 'type' => 'expense', 'icon' => 'medical_services', 'color' => '#EF4444'],
            ['name' => 'Hiburan', 'type' => 'expense', 'icon' => 'movie', 'color' => '#6366F1'],
            ['name' => 'Sedekah / Write-off', 'type' => 'expense', 'icon' => 'volunteer_activism', 'color' => '#14B8A6'],
            ['name' => 'Lainnya (Pengeluaran)', 'type' => 'expense', 'icon' => 'category', 'color' => '#64748B'],

            // Income categories
            ['name' => 'Gaji', 'type' => 'income', 'icon' => 'payments', 'color' => '#10B981'],
            ['name' => 'Bonus & Freelance', 'type' => 'income', 'icon' => 'work', 'color' => '#22C55E'],
            ['name' => 'Dividen & Investasi', 'type' => 'income', 'icon' => 'trending_up', 'color' => '#1B4332'],
            ['name' => 'Hadiah & Lainnya', 'type' => 'income', 'icon' => 'redeem', 'color' => '#06B6D4'],
        ];
    }

    /**
     * Seed default categories idempotently for a specific user.
     */
    public static function seedForUser(User $user): void
    {
        foreach (self::defaultCategories() as $item) {
            Category::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => $item['name'],
                    'type' => $item['type'],
                ],
                [
                    'icon' => $item['icon'],
                    'color' => $item['color'],
                ]
            );
        }
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->each(function (User $user): void {
            self::seedForUser($user);
        });
    }
}
