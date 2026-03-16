<?php

namespace App\Models\Apify;

use App\Enums\Apify\JobStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Job extends Model
{
    protected $table = 'apify_jobs';

    protected $casts = [
        'job_options' => 'array',
        'job_data' => 'array',
        'job_result' => 'array',
        'price' => 'float',
    ];

    protected $fillable = [
        'actor',
        'user_id',
        'team_id',
        'job_options',
        'job_data',
        'job_result',
        'job_id',
        'job_status',
        'job_error',
        'price',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getItemsCountAttribute(): int
    {
        if (isset($this->job_data['status']) && $this->job_data['status'] === JobStatus::SUCCEEDED->value) 
        {
            return count($this->job_result);
        }

        return 0;
    }
}
