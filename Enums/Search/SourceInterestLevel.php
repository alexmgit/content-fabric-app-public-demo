<?php

namespace App\Enums\Search;

enum SourceInterestLevel: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case VERY_HIGH = 'very_high';
    case EXCELLENT = 'excellent';

    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Низкий',
            self::MEDIUM => 'Средний',
            self::HIGH => 'Высокий',
            self::VERY_HIGH => 'Очень высокий',
            self::EXCELLENT => 'Отличный',
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