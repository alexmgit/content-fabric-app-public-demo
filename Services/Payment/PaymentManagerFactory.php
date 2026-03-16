<?php

namespace App\Services\Payment;

use YooKassa\Client;

class PaymentManagerFactory
{
    public function make(Client $client): PaymentManager
    {
        return new PaymentManager($client);
    }
}
