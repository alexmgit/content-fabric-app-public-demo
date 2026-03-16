<?php

namespace App\Models\Search;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
     use HasFactory;
     use SoftDeletes;
 
     protected $table = 'search_events';

     protected $casts = [
         'is_active' => 'boolean',
         'data' => 'array',
         'tags' => 'array',
     ];
 
     protected $fillable = [
         'name',
         'description',
         'is_active',
         'search_id',
         'search_run_id',
         'user_id',
         'team_id',
         'data',
         'tags',
     ];
 
     public function search(): BelongsTo
     {
         return $this->belongsTo(Search::class);
     }
}
