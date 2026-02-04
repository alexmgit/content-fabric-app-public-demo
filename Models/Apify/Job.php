<?php

namespace App\Models\Apify;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Enums\Apify\JobStatus;

class Job extends Model
{
    protected $table = 'apify_jobs';

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getJobOptionsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function getJobDataAttribute($value)
    {
        return json_decode($value, true);
    }

    public function getJobResultAttribute($value)
    {
        return json_decode($value, true);
    }

    public function getItemsCountAttribute()
    {
        if (isset($this->job_data['status']) && $this->job_data['status'] === JobStatus::SUCCEEDED->value) 
        {
            return count($this->job_result);
        }

        return 0;
    }
}
