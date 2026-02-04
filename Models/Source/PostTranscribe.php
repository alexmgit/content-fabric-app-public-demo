<?php

namespace App\Models\Source;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Apify\Job;

class PostTranscribe extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'source_post_transcribes';

    protected $fillable = [
        'job_id',
        'post_id',
        'file_url',
        'transcription',
        'result',
        'status',
        'user_id',
        'team_id',
    ];

    protected $appends = [
        'analize_result',
    ];

    public function job() : BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }


    public function getAnalizeResultAttribute()
    {
        $data = json_decode($this->result, true);

        return [
            'hook' => $data['hook'] ?? '', 
            'viral_reasons' => $data['viral_reasons'] ?? '', 
            'strong_points' => $data['strong_points'] ?? '', 
            'weak_points' => $data['weak_points'] ?? '', 
            'tags' => $data['tags'] ?? [],
        ];
    }
}
