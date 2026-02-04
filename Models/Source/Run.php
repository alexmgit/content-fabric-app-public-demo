<?php

namespace App\Models\Source;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Apify\Job;

class Run extends Model
{
    protected $table = 'source_runs';
    
    protected $fillable = [
        'source_id',
        'status',
        'user_id',
        'team_id',
        'profile_job_id',
        'post_job_id',
        'is_post_processed',
    ];

    public function source() : BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function profileJob() : BelongsTo
    {
        return $this->belongsTo(Job::class, 'profile_job_id');
    }

    public function postJob() : BelongsTo
    {
        return $this->belongsTo(Job::class, 'post_job_id');
    }
}
