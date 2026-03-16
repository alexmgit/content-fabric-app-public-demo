<?php

namespace App\Http\Resources\Billing;

class PlanFeatureResource
{
    public static function collection(iterable $features): array
    {
        $items = [];

        foreach ($features as $feature) {
            $items[] = self::make($feature);
        }

        return $items;
    }

    public static function make(object $feature): array
    {
        return [
            'id' => $feature->id ?? null,
            'slug' => $feature->slug ?? null,
            'name' => $feature->name ?? null,
            'value' => $feature->value ?? null,
            'description' => $feature->description ?? null,
            'sort_order' => $feature->sort_order ?? null,
        ];
    }
}
