<?php

namespace App\Services\Apify;

use Carbon\Carbon;

interface SearchPostParserInterface
{
    public function url(): string;

    public function ownerUsername(): string;

    public function likesCount(): int;

    public function commentsCount(): int;

    public function viewsCount(): int;

    public function location(): string;

    public function type(): string;

    public function createdAt(): Carbon;

    public function sharesCount(): int;

    public function caption(): string;

    public function videoFileUrl(): string;
}