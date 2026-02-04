<?php

namespace App\Models\Search;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class Source extends Model
{
    use SoftDeletes;

    protected $table = 'searches_sources';

    protected $fillable = [
        'search_id',
        'run_id',
        'user_id',
        'team_id',
        'source_type',
        'source_url',
        'source_follows_count',
        'source_followers_count',
        'source_posts_count',
        'hash',
        'search_views_count',
        'search_likes_count',
        'search_comments_count',
        'interest_level',
    ];

    public function search() : BelongsTo    
    {
        return $this->belongsTo(Search::class);
    }

    public function run() : BelongsTo
    {
        return $this->belongsTo(Run::class);
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
