<?php

namespace App\Services\Adapters;

use App\Contracts\Sleeper;

class SystemSleeper implements Sleeper
{
    public function sleep(int $seconds): void
    {
        sleep($seconds);
    }
}
