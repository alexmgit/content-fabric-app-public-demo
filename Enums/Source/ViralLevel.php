<?php

namespace App\Enums\Source;

enum ViralLevel: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case VIRAL = 'viral';

    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Низкая',
            self::MEDIUM => 'Средняя',
            self::HIGH => 'Высокая',
            self::VIRAL => 'Вирусная',
        };
    }

    public static function valueFromName(string $name): self
    {
        foreach (self::cases() as $case) {
            if ($case->value === $name) {
                return $case;
            }
        }
        return self::LOW;
    }
}