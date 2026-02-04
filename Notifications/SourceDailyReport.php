<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SimpleMessage;
use App\Notifications\Channels\TelegramNotification;
use App\Notifications\Channels\TelegramChannel;

class SourceDailyReport extends TelegramNotification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    public function via(object $notifiable): string
    {
        return TelegramChannel::class;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toTelegram(object $notifiable): SimpleMessage
    {
        return (new SimpleMessage)
            ->line('Hello, world!');
    }
}
