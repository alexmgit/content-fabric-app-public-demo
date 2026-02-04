<?php

namespace App\Services;

use App\Models\Search\Source;
use App\Enums\Search\SourceInterestLevel;

class SourceInterestDetector
{
    public function detect(Source $source): SourceInterestLevel
    {
        $viewsCount = $source->search_views_count;


        if ($viewsCount > 1000000) {
            return SourceInterestLevel::EXCELLENT;
        }

        if ($viewsCount > 100000) {
            return SourceInterestLevel::VERY_HIGH;
        }

        if ($viewsCount > 10000) {
            return SourceInterestLevel::HIGH;
        }

        if ($viewsCount > 1000) {
            return SourceInterestLevel::MEDIUM;
        }

        return SourceInterestLevel::LOW;
    }
}