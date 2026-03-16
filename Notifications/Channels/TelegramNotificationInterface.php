<?php

namespace App\Notifications\Channels;

use App\Models\User;
use Illuminate\Notifications\Messages\SimpleMessage;

interface TelegramNotificationInterface
{
    public function toTelegram(User $notifiable): SimpleMessage;
}