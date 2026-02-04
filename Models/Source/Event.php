<?php

namespace App\Models\Source;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Source\Source;

class Event extends Model
{
    //
    use HasFactory;
    use SoftDeletes;

    protected $table = 'source_events';

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

    public function source()
    {
        return $this->belongsTo(Source::class);
    }
}
