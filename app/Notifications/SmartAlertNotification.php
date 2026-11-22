<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SmartAlertNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param array{
     *     type: string,
     *     title: string,
     *     message: string,
     *     icon: string,
     *     color: string,
     *     action_url: string,
     *     action_label: string,
     *     category_id?: int|string|null,
     *     debt_id?: int|string|null,
     *     goal_id?: int|string|null,
     * } $data
     */
    public function __construct(
        public readonly array $data
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->data;
    }
}
