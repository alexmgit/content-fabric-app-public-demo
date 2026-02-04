<?php

namespace App\Models\Source;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use DateInterval;
use App\Enums\Source\SourceInputType;

class Source extends Model
{
    /** @use HasFactory<\Database\Factories\SourceFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $table = 'sources';

    protected $casts = [
        'last_parsed_at' => 'datetime',
    ];

    protected $fillable = [
        'name',
        'description',
        'url',
        'type',
        'source_type',
        'is_active',
        'user_id',
        'team_id',
        'last_parsed_at',
        'tags',
        'post_parse_count',
        'post_schedule_type',
        'post_schedule_period',
        'post_schedule_days',
    ];

    protected $appends = [
        'next_parsed_at',
        'real_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTagsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setTagsAttribute($value)
    {
        $this->attributes['tags'] = is_array($value) ? json_encode($value) : $value;
    }

    public function getIsActiveAttribute($value)
    {
        return (bool) $value;
    }

    public function getPostScheduleDaysAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setPostScheduleDaysAttribute($value)
    {
        $this->attributes['post_schedule_days'] = is_array($value) ? json_encode($value) : $value;
    }

    public function getNextParsedAtAttribute()
    {
        if ($this->last_parsed_at === null) {   
            return Date::parse($this->created_at)->add(new DateInterval('PT5M'));
        }

        $lastParsedAt = Date::parse($this->last_parsed_at);

        if (config('app.is_use_plans')) {
            $period = $this->buildInterval('24h');
        } else {
            $period = $this->buildInterval($this->post_schedule_period);
        }

        $nextParsedAt = $lastParsedAt->add($period);

        return $nextParsedAt;
    }

    public function getRealUrlAttribute()
    {
        return match ($this->source_type) {
            SourceInputType::MANUAL->value => $this->url,
            SourceInputType::SEARCH_HASHTAG->value => 'https://www.instagram.com/explore/tags/' . $this->url,
            default => $this->url,
        };
    }

    public function buildInterval($period)
    {
        preg_match('/^(?<count>\d+)(?<period>[hd])$/', $period, $matches);

        if ($matches['period'] === 'h') {
            return new DateInterval('PT' . $matches['count'] . 'H');
        } elseif ($matches['period'] === 'd') {
            return new DateInterval('P' . $matches['count'] . 'D');
        }

        throw new \Exception('Invalid post schedule period');
    }
}
