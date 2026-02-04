<?php

namespace App\Services\Adapters;

use App\Contracts\Mailer;
use Illuminate\Support\Facades\Mail;

class LaravelMailer implements Mailer
{
    public function send($recipient, $mailable): void
    {
        Mail::to($recipient)->send($mailable);
    }
}
