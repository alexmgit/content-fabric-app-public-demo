<?php

namespace App\Enums\Billing;

enum YooKassaPaymentStatus: string
{
    case SUCCEEDED = 'succeeded';
    case CANCELED = 'canceled';
}
