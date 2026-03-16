<?php

namespace App\Enums\Source;

enum SourceTypes: string
{
    case INSTAGRAM = 'instagram';
    case YOUTUBE = 'youtube';
    case TIKTOK = 'tiktok';

    public static function resolve(string $type): self
    {
        if (stripos($type, 'instagram.com') !== false || 
            stripos($type, 'instagram.app') !== false ||
            stripos($type, 'instagram.net') !== false) {
            return self::INSTAGRAM;
        }

        if (stripos($type, 'youtube.com') !== false || 
            stripos($type, 'youtu.be') !== false ||
            stripos($type, 'youtube.app') !== false ||
            stripos($type, 'youtube.net') !== false) {
            return self::YOUTUBE;
        }

        if (stripos($type, 'tiktok.com') !== false || 
            stripos($type, 'tiktok.app') !== false ||
            stripos($type, 'tiktok.net') !== false) {
            return self::TIKTOK;
        }

        throw new \Exception('Invalid source type: ' . $type);
    }

    public static function makeUrl(string $type, string $account): string
    {
        if ($type === self::INSTAGRAM->value) {
            return 'https://www.instagram.com/' . $account;
        }

        if ($type === self::YOUTUBE->value) {
            return $account;
        }

        if ($type === self::TIKTOK->value) {
            return 'https://www.tiktok.com/@' . $account;
        }

        throw new \Exception('Invalid source type: ' . $type);
    }
}