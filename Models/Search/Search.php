<?php

namespace App\Models\Search;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Date;
use App\Models\User;
use DateInterval;

class Search extends Model
{
    // /** @use HasFactory<\Database\Factories\SourceFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
        'last_parsed_at' => 'datetime',
    ];

    protected $table = 'searches';

    protected $fillable = [
        'name',
        'description',
        'search_type',
        'query',
        'query_type',
        'user_id',
        'team_id',
        'last_parsed_at',
        'tags',
        'is_active',
        'parse_count',
        'schedule_type',
        'schedule_period',
        'schedule_days',
    ];

    protected $appends = [
        'next_parsed_at',
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

    public function getScheduleDaysAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setScheduleDaysAttribute($value)
    {
        $this->attributes['schedule_days'] = is_array($value) ? json_encode($value) : $value;
    }

    public function getNextParsedAtAttribute()
    {
        if ($this->last_parsed_at === null) {
            return Date::parse($this->created_at)->add(new DateInterval('PT5M'));
        }

        $lastParsedAt = Date::parse($this->last_parsed_at);

        $period = $this->buildInterval($this->schedule_period);

        $nextParsedAt = $lastParsedAt->add($period);

        return $nextParsedAt;
    }

    public function buildInterval($period)
    {
        preg_match('/^(?<count>\d+)(?<period>[hd])$/', $period, $matches);

        if ($matches['period'] === 'h') {
            return new DateInterval('PT' . $matches['count'] . 'H');
        } elseif ($matches['period'] === 'd') {
            return new DateInterval('P' . $matches['count'] . 'D');
        }

        throw new \Exception('Invalid schedule period');
    }   
}
