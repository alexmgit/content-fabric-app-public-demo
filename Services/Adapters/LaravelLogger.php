<?php

namespace App\Services\Adapters;

use App\Contracts\Logger;
use Illuminate\Support\Facades\Log;

class LaravelLogger implements Logger
{
    public function info(string $message, array $context = []): void
    {
        Log::info($message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        Log::error($message, $context);
    }
}
