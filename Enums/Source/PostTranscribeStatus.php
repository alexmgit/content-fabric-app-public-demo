<?php

namespace App\Enums\Source;

enum PostTranscribeStatus: string
{
    case WAITING = 'waiting';
    case COMPLETE = 'complete';
    case FAILED = 'failed';
}
