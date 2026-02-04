<?php

namespace App\Enums\Apify;

enum JobStatus: string
{
    case CREATED = 'CREATED';
    case SUCCEEDED = 'SUCCEEDED';
    case FAILED = 'FAILED';
}
