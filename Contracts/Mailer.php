<?php

namespace App\Contracts;

interface Mailer
{
    public function send($recipient, $mailable): void;
}
