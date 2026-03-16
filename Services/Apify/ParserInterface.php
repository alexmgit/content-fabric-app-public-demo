<?php

namespace App\Services\Apify;

interface ParserInterface
{
    public static function parse(array $data): self;

    public function canParse(string $type, array $data): bool;
}