<?php

namespace App\Enums\Source;

enum RunStatus: string
{
    case WAITING = 'waiting';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}
