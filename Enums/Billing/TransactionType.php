<?php

namespace App\Enums\Billing;

enum TransactionType: string
{
    case PAYMENT = 'payment';
    case REFUND = 'refund';
    case PLAN_PAYMENT = 'plan_payment';
    case PLAN_REFUND = 'plan_refund';

    public function label(): string
    {
        return match ($this) {
            self::PAYMENT => 'Пополнение баланса',
            self::REFUND => 'Возврат средств',
            self::PLAN_PAYMENT => 'Оплата подписки',
            self::PLAN_REFUND => 'Возврат средств за подписку',
        };
    }
}