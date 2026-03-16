<?php

namespace App\Models\Source;

use App\Enums\Source\ViralLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\User;
use App\Models\Team;
use App\Models\Source\Run;  
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'source_posts';

    protected $fillable = [
        'source_id',
        'run_id',
        'user_id',
        'team_id',
        'profile_url',
        'profile_followers_count',
        'post_type',
        'post_caption',
        'post_created_at',
        'post_likes_count',
        'post_likes_count_avg',
        'post_likes_count_median',
        'post_comments_count',
        'post_comments_count_avg',
        'post_comments_count_median',
        'post_views_count',
        'post_views_count_avg',
        'post_views_count_median',
        'post_shared_count',
        'post_shared_count_avg',
        'post_shared_count_median',
        'post_hash',
        'post_url',
        'post_location',
        'viral_level',

        'metric_engagement_rate',
        'metric_engagement_rate_followers',
        'metric_views_followers_ratio',
        'metric_likes_views_ratio',
        'metric_comments_views_ratio',
        'metric_engagement_velocity',
        'metric_quality_score',
        'metric_viral_level',
    ];

    protected $casts = [
        'post_created_at' => 'datetime',
        'profile_followers_count' => 'integer',
        'post_likes_count' => 'integer',
        'post_likes_count_avg' => 'integer',
        'post_likes_count_median' => 'integer',
        'post_comments_count' => 'integer',
        'post_comments_count_avg' => 'integer',
        'post_comments_count_median' => 'integer',
        'post_views_count' => 'integer',
        'post_views_count_avg' => 'integer',
        'post_views_count_median' => 'integer',
        'post_shared_count' => 'integer',
        'post_shared_count_avg' => 'integer',
        'post_shared_count_median' => 'integer',
        'metric_engagement_rate' => 'float',
        'metric_engagement_rate_followers' => 'float',
        'metric_views_followers_ratio' => 'float',
        'metric_likes_views_ratio' => 'float',
        'metric_comments_views_ratio' => 'float',
        'metric_engagement_velocity' => 'float',
        'metric_quality_score' => 'float',
    ];

    protected $appends = [
        'can_analize',
    ];

    public function run()
    {
        return $this->belongsTo(Run::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function source()
    {
        return $this->belongsTo(Source::class);
    }

    public function transcribe(): HasOne
    {
        return $this->hasOne(PostTranscribe::class, 'post_id');
    }

    public function getCanAnalizeAttribute(): bool
    {
        return in_array($this->post_type, ['video', 'Video']) && in_array($this->metric_viral_level, [
            ViralLevel::VIRAL->value,
            ViralLevel::HIGH->value,
            ViralLevel::MEDIUM->value,
        ]);
    }


}
