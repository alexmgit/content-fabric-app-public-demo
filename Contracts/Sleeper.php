<?php

namespace App\Contracts;

interface Sleeper
{
    public function sleep(int $seconds): void;
}
