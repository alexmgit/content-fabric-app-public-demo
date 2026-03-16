<?php

namespace App\Enums\Billing;

enum TransactionDirection: string
{
    case IN = 'in';
    case OUT = 'out';
}