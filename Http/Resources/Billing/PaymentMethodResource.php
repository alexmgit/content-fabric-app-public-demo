<?php

namespace App\Http\Resources\Billing;

use App\Models\Billing\PaymentMethod;

class PaymentMethodResource
{
    public static function collection(iterable $methods): array
    {
        $items = [];

        foreach ($methods as $method) {
            $items[] = self::make($method);
        }

        return $items;
    }

    public static function make(PaymentMethod $method): array
    {
        return [
            'id' => $method->id,
            'is_default' => (bool) $method->is_default,
            'is_active' => (bool) $method->is_active,
            'payment_method_title' => $method->payment_method_title,
            'created_at' => $method->created_at,
        ];
    }
}
