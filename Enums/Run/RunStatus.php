<?php

namespace App\Enums\Run;

enum RunStatus: string
{
    case WAITING = 'waiting';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}
