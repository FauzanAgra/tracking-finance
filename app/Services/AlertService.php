<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Debt;
use App\Models\Goal;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\SmartAlertNotification;
use Illuminate\Notifications\DatabaseNotification;

class AlertService
{
    /**
     * Inspect financial health, thresholds, and deadlines for a user, then dispatch smart alerts.
     *
     * @return int Number of new notifications dispatched.
     */
    public function checkUserAlerts(User $user): int
    {
        $dispatched = 0;

        // 1. Check Budgets (>80% warning and >100% exceeded)
        $currentMonth = now()->format('Y-m');
        $budgets = Budget::query()
            ->where('user_id', $user->id)
            ->where('month_year', $currentMonth)
            ->with('category')
            ->get();

        $recentNotifications = $user->notifications()
            ->where('created_at', '>=', now()->startOfMonth())
            ->get();

        foreach ($budgets as $budget) {
            $limit = (float) $budget->amount;
            if ($limit <= 0) {
                continue;
            }

            $spent = (float) Transaction::query()
                ->where('user_id', $user->id)
                ->where('category_id', $budget->category_id)
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [
                    now()->startOfMonth()->toDateString(),
                    now()->endOfMonth()->toDateString(),
                ])
                ->sum('amount');

            $pct = ($spent / $limit) * 100;

            if ($pct >= 80) {
                $categoryName = $budget->category?->name ?? 'Kategori';
                $isExceeded = $pct >= 100;
                $targetType = $isExceeded ? 'budget_exceeded' : 'budget_warning';

                $alreadySent = $recentNotifications->contains(function (DatabaseNotification $n) use ($budget, $targetType) {
                    return ($n->data['category_id'] ?? null) == $budget->category_id
                        && ($n->data['type'] ?? '') === $targetType;
                });

                if (! $alreadySent) {
                    $title = $isExceeded ? 'Batas Budget Terlampaui! ⚠️' : 'Peringatan Budget Menipis (80%+) ⏳';
                    $color = $isExceeded ? 'rose' : 'amber';
                    $icon = $isExceeded ? 'warning' : 'pie_chart';

                    $user->notify(new SmartAlertNotification([
                        'type' => $targetType,
                        'title' => $title,
                        'message' => "Pengeluaran {$categoryName} telah mencapai " . number_format($pct, 0) . "% (Rp " . number_format($spent, 0, ',', '.') . " dari kuota Rp " . number_format($limit, 0, ',', '.') . ").",
                        'icon' => $icon,
                        'color' => $color,
                        'action_url' => route('budgets.index', absolute: false),
                        'action_label' => 'Lihat Budget',
                        'category_id' => $budget->category_id,
                    ]));

                    $dispatched++;
                }
            }
        }

        // 2. Check Debts & Receivables Due Dates (<= 3 days)
        $debts = Debt::query()
            ->where('user_id', $user->id)
            ->whereNotIn('status', ['paid', 'written_off'])
            ->whereNotNull('due_date')
            ->with('payments')
            ->get();

        $todayNotifications = $user->notifications()
            ->whereDate('created_at', now()->toDateString())
            ->get();

        foreach ($debts as $debt) {
            $alreadyPaid = (float) $debt->payments->sum('amount');
            $remaining = max(0, (float) $debt->total_amount - $alreadyPaid - (float) $debt->written_off_amount);

            if ($remaining <= 0) {
                continue;
            }

            $daysLeft = (int) now()->startOfDay()->diffInDays($debt->due_date->startOfDay(), false);

            if ($daysLeft <= 3 && $daysLeft >= -7) {
                $alreadySent = $todayNotifications->contains(function (DatabaseNotification $n) use ($debt) {
                    return ($n->data['debt_id'] ?? null) == $debt->id;
                });

                if (! $alreadySent) {
                    $isPayable = $debt->type === 'payable';
                    $roleText = $isPayable ? 'Utang kepada' : 'Piutang dari';
                    $dueText = $daysLeft === 0
                        ? 'hari ini'
                        : ($daysLeft > 0 ? "dalam {$daysLeft} hari" : 'terlambat ' . abs($daysLeft) . ' hari');

                    $user->notify(new SmartAlertNotification([
                        'type' => 'debt_due',
                        'title' => "Pengingat Jatuh Tempo: {$debt->person_name}",
                        'message' => "{$roleText} {$debt->person_name} sebesar Rp " . number_format($remaining, 0, ',', '.') . " jatuh tempo {$dueText} ({$debt->due_date->format('d/m/Y')}).",
                        'icon' => 'event_upcoming',
                        'color' => $daysLeft < 0 ? 'rose' : 'amber',
                        'action_url' => route('debts.index', absolute: false),
                        'action_label' => 'Kelola Utang',
                        'debt_id' => $debt->id,
                    ]));

                    $dispatched++;
                }
            }
        }

        // 3. Check Goals Reached (100% Target Amount)
        $goals = Goal::query()
            ->where('user_id', $user->id)
            ->get();

        $allNotifications = $user->notifications()->get();

        foreach ($goals as $goal) {
            $target = (float) $goal->target_amount;
            $current = (float) $goal->current_amount;

            if ($target > 0 && $current >= $target) {
                $alreadySent = $allNotifications->contains(function (DatabaseNotification $n) use ($goal) {
                    return ($n->data['goal_id'] ?? null) == $goal->id
                        && ($n->data['type'] ?? '') === 'goal_reached';
                });

                if (! $alreadySent) {
                    $user->notify(new SmartAlertNotification([
                        'type' => 'goal_reached',
                        'title' => 'Target Impian Tercapai! 🎉',
                        'message' => "Selamat! Tabungan impian \"{$goal->title}\" telah berhasil terkumpul 100% (Rp " . number_format($current, 0, ',', '.') . ").",
                        'icon' => 'military_tech',
                        'color' => 'emerald',
                        'action_url' => route('goals.index', absolute: false),
                        'action_label' => 'Lihat Impian',
                        'goal_id' => $goal->id,
                    ]));

                    $dispatched++;
                }
            }
        }

        return $dispatched;
    }

    /**
     * Check smart alerts for all registered users across the application.
     */
    public function checkAllAlerts(): int
    {
        $total = 0;
        User::query()->chunk(50, function ($users) use (&$total): void {
            foreach ($users as $user) {
                $total += $this->checkUserAlerts($user);
            }
        });

        return $total;
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(User $user, string $notificationId): bool
    {
        $notification = $user->notifications()->where('id', $notificationId)->first();
        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead(User $user): int
    {
        return $user->unreadNotifications()->update(['read_at' => now()]);
    }

    /**
     * Delete all notifications for the user.
     */
    public function clearAll(User $user): int
    {
        return $user->notifications()->delete();
    }
}
