<?php

namespace App\Models\Source;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'source_events';

    protected $casts = [
        'is_active' => 'boolean',
        'data' => 'array',
        'tags' => 'array',
    ];

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'source_id',
        'source_run_id',
        'user_id',
        'team_id',
        'data',
        'tags',
        'hash',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }
}
