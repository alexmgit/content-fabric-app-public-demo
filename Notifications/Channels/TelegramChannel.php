<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use App\Models\User;
use TelegramBot\Api\BotApi;

class TelegramChannel extends Notification
{
    public function send(User $notifiable, TelegramNotification $notification): void
    {
        if (!$notifiable->telegram) {
            return;
        }

        $message = $notification->toTelegram($notifiable);

        $client = new BotApi(config('services.telegram_notifications.bot_token'));
        $client->sendMessage($notifiable->telegram->chat_id, implode("\n", $message->introLines));

        $message->success();
    }
}