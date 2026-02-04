<?php

namespace App\Models\Search;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Apify\Job;

class Run extends Model
{
    protected $table = 'searches_runs';
    
    protected $fillable = [
        'search_id',
        'status',
        'user_id',
        'team_id',
        'search_job_id',
        'source_job_id',
        'is_post_processed',
    ];

    public function search() : BelongsTo
    {
        return $this->belongsTo(Search::class);
    }

    public function searchJob() : BelongsTo
    {
        return $this->belongsTo(Job::class, 'search_job_id');
    }

    public function sourceJob() : BelongsTo
    {
        return $this->belongsTo(Job::class, 'source_job_id');
    }
}
