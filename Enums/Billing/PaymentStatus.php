<?php

namespace App\Enums\Billing;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case WAITING = 'waiting';
    case PROCESSING = 'processing';
    case PAID = 'paid';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Ожидание',
            self::WAITING => 'Ожидание',
            self::PROCESSING => 'Обработка',
            self::PAID => 'Оплачено',
            self::FAILED => 'Неудачно',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'text-yellow-500',
            self::WAITING => 'text-yellow-500',
            self::PROCESSING => 'text-yellow-500',
            self::PAID => 'text-green-500',
            self::FAILED => 'text-red-500',
        };
    }
}