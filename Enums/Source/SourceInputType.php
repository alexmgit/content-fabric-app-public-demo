<?php

namespace App\Enums\Source;

enum SourceInputType: string
{
    case MANUAL = 'manual';
    case SEARCH_HASHTAG = 'search-hashtag';
}
