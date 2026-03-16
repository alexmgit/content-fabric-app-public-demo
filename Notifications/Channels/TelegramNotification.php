<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use App\Models\User;
use Illuminate\Notifications\Messages\SimpleMessage;

abstract class TelegramNotification extends Notification implements TelegramNotificationInterface
{
    abstract public function toTelegram(User $notifiable): SimpleMessage;
}