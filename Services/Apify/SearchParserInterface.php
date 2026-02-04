<?php

namespace App\Services\Apify;

interface SearchParserInterface
{
    public function url(): string;

    public function posts(): array;
}