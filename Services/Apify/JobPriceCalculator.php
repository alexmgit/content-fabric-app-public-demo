<?php

namespace App\Services\Apify;

use App\Enums\Apify\JobStatus;
use App\Models\Apify\Job;

class JobPriceCalculator
{
    public function getPrice(Job $job): float
    {
        if (isset($job->job_data['status']) && $job->job_data['status'] === JobStatus::SUCCEEDED->value) 
        {
            $model = $job->job_data['pricingInfo']['pricingModel'] ?? null;

            if ($model === 'PRICE_PER_DATASET_ITEM' && isset($job->job_data['pricingInfo']['pricePerUnitUsd'])) 
            {
                $itemsCount = count($job->job_result);

                return round($job->job_data['pricingInfo']['pricePerUnitUsd'] * $itemsCount, 2);
            }
        }

        return 0.0;
    }
}
