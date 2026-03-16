<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SimpleMessage;
use Laravelcm\Subscriptions\Models\Plan;
use App\Notifications\Channels\TelegramNotification;
use App\Notifications\Channels\TelegramChannel;

class PlanApply extends TelegramNotification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Plan $plan)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [TelegramChannel::class];
    }

    public function toTelegram(object $notifiable): SimpleMessage
    {
        return (new SimpleMessage)
            ->line('Тариф ' . $this->plan->name . ' подключен');
    }
}
