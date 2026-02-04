<?php

namespace App\Enums\Billing;

enum SubscriptionPaymentStatus: string
{
    case WAITING = 'waiting';
    case FAILED = 'failed';
    case PAID = 'paid';
}
