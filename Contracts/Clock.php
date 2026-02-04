<?php

namespace App\Contracts;

use Illuminate\Support\Carbon;

interface Clock
{
    public function now(): Carbon;
}
