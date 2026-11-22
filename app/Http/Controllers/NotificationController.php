<?php

namespace App\Http\Controllers;

use App\Services\AlertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        private readonly AlertService $alertService
    ) {}

    /**
     * Display a list of financial notifications and alerts.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Check and generate fresh alerts if thresholds are met
        $this->alertService->checkUserAlerts($user);

        $filter = (string) $request->query('filter', 'all');
        $query = $user->notifications();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        }

        $notifications = $query->latest()->paginate(15)->withQueryString();
        $unreadCount = $user->unreadNotifications()->count();

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'filter' => $filter,
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, string $id): RedirectResponse
    {
        $this->alertService->markAsRead($request->user(), $id);

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead(Request $request): RedirectResponse
    {
        $count = $this->alertService->markAllAsRead($request->user());

        return back()->with('success', "{$count} notifikasi berhasil ditandai dibaca.");
    }

    /**
     * Clear all notifications for the user.
     */
    public function clear(Request $request): RedirectResponse
    {
        $this->alertService->clearAll($request->user());

        return back()->with('success', 'Semua riwayat notifikasi telah dibersihkan.');
    }
}
