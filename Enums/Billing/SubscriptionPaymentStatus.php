<?php

namespace App\Enums\Billing;

enum SubscriptionPaymentStatus: string
{
    case WAITING = 'waiting';
    case PAID = 'paid';
    case FAILED = 'failed';
}
