<?php

namespace App\Services\Adapters;

use App\Contracts\Clock;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

class LaravelClock implements Clock
{
    public function now(): Carbon
    {
        return Date::now();
    }
}
