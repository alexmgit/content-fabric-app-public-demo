<?php

namespace App\Services\Apify;

use Carbon\Carbon;

interface ProfileParserInterface
{
    /**
     * @description Показывает url профиля
     * @return string
     */
    public function url(): string;

    /**
     * @description Показывает количество подписок пользователя
     * @return int
     */
    public function followsCount(): int;

    /**
     * @description Показывает количество подписок пользователя
     * @return int
     */
    public function followersCount(): int;

    /**
     * @description Показывает количество постов пользователя
     * @return int
     */
    public function postsCount(): int;
}