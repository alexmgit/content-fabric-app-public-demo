<?php

namespace App\Services\Apify;

use Carbon\Carbon;

interface PostParserInterface
{
    public function url(): string;

    public function type(): string;

    public function location(): string;

    public function caption(): string;

    public function createdAt(): Carbon;

    public function hashtags(): array;

    public function commentsCount(): int;

    public function likesCount(): int;

    public function viewsCount(): int;

    public function sharesCount(): int;

    public function videoFileUrl(): string;
}