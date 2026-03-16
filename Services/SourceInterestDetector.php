<?php

namespace App\Services;

use App\Models\Search\Source;
use App\Enums\Search\SourceInterestLevel;

class SourceInterestDetector
{
    private const VIEW_THRESHOLDS = [
        SourceInterestLevel::EXCELLENT->value => 1000000,
        SourceInterestLevel::VERY_HIGH->value => 100000,
        SourceInterestLevel::HIGH->value => 10000,
        SourceInterestLevel::MEDIUM->value => 1000,
    ];

    public function detect(Source $source): SourceInterestLevel
    {
        foreach (self::VIEW_THRESHOLDS as $level => $threshold) {
            if ($source->search_views_count > $threshold) {
                return SourceInterestLevel::from($level);
            }
        }

        return SourceInterestLevel::LOW;
    }
}
