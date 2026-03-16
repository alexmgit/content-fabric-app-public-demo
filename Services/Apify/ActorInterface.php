<?php

namespace App\Services\Apify;

use App\Services\Apify\RunActorResult;

interface ActorInterface
{
    public function getActorId(): string;

    public function run(array $options = []): RunActorResult;

    public function parseDatasetItems(array $items): array;
}