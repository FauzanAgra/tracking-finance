<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Debt;
use App\Models\Goal;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\AlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_notifications(): void
    {
        $response = $this->get(route('notifications.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_budget_threshold_alert_is_dispatched_when_usage_exceeds_80_percent(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => 'bank',
            'currency' => 'IDR',
            'balance' => 5000000,
        ]);

        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Makan & Minum',
            'type' => 'expense',
        ]);

        // Budget of 1,000,000 for current month
        Budget::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => 1000000,
            'month_year' => now()->format('Y-m'),
        ]);

        // Expense of 850,000 (85%)
        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 850000,
            'transaction_date' => now()->toDateString(),
            'description' => 'Makan Enak',
        ]);

        $alertService = app(AlertService::class);
        $count = $alertService->checkUserAlerts($user);

        $this->assertEquals(1, $count);
        $this->assertEquals(1, $user->notifications()->count());

        $notification = $user->notifications()->first();
        $this->assertEquals('budget_warning', $notification->data['type']);
        $this->assertStringContainsString('Peringatan Budget Menipis', $notification->data['title']);
        $this->assertStringContainsString('85%', $notification->data['message']);

        // Check view
        $response = $this->actingAs($user)->get(route('notifications.index'));
        $response->assertOk();
        $response->assertSee('Pusat Notifikasi');
        $response->assertSee('Makan &amp; Minum', false);
    }

    public function test_debt_due_date_alert_is_dispatched_when_due_within_three_days(): void
    {
        $user = User::factory()->create();

        // Debt due in 2 days
        Debt::create([
            'user_id' => $user->id,
            'person_name' => 'Mas Rudi',
            'type' => 'payable',
            'currency' => 'IDR',
            'total_amount' => 1500000,
            'issue_date' => now()->subDays(10)->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
            'status' => 'unpaid',
        ]);

        $alertService = app(AlertService::class);
        $count = $alertService->checkUserAlerts($user);

        $this->assertEquals(1, $count);
        $this->assertEquals(1, $user->notifications()->count());

        $notification = $user->notifications()->first();
        $this->assertEquals('debt_due', $notification->data['type']);
        $this->assertStringContainsString('Mas Rudi', $notification->data['title']);
    }

    public function test_goal_completion_alert_is_dispatched_when_target_reached(): void
    {
        $user = User::factory()->create();

        Goal::create([
            'user_id' => $user->id,
            'title' => 'Dana Darurat 3 Bulan',
            'currency' => 'IDR',
            'target_amount' => 10000000,
            'current_amount' => 10000000,
            'status' => 'achieved',
        ]);

        $alertService = app(AlertService::class);
        $count = $alertService->checkUserAlerts($user);

        $this->assertEquals(1, $count);
        $notification = $user->notifications()->first();
        $this->assertEquals('goal_reached', $notification->data['type']);
        $this->assertStringContainsString('Target Impian Tercapai', $notification->data['title']);
    }

    public function test_user_can_mark_notification_as_read_and_clear_all(): void
    {
        $user = User::factory()->create();

        // Trigger goal alert
        Goal::create([
            'user_id' => $user->id,
            'title' => 'Beli Laptop',
            'currency' => 'IDR',
            'target_amount' => 5000000,
            'current_amount' => 5000000,
        ]);

        app(AlertService::class)->checkUserAlerts($user);

        $notification = $user->unreadNotifications()->first();
        $this->assertNotNull($notification);

        // Mark as read
        $response = $this->actingAs($user)->patch(route('notifications.read', $notification->id));
        $response->assertRedirect();
        $this->assertEquals(0, $user->unreadNotifications()->count());

        // Clear all
        $response = $this->actingAs($user)->delete(route('notifications.clear'));
        $response->assertRedirect();
        $this->assertEquals(0, $user->notifications()->count());
    }

    public function test_console_command_finance_check_alerts(): void
    {
        $user = User::factory()->create();

        Goal::create([
            'user_id' => $user->id,
            'title' => 'Dana Liburan',
            'currency' => 'IDR',
            'target_amount' => 2000000,
            'current_amount' => 2000000,
        ]);

        $this->artisan('finance:check-alerts')
            ->expectsOutputToContain('Pemeriksaan selesai')
            ->assertExitCode(0);

        $this->assertEquals(1, $user->notifications()->count());
    }
}
