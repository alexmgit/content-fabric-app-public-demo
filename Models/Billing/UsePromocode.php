<?php

namespace App\Models\Billing;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsePromocode extends Model
{
    protected $table = 'billing_use_promocode';

    protected $fillable = [
        'user_id',
        'team_id',
        'promocode',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function delete()
    {
        return false;
    }
}
