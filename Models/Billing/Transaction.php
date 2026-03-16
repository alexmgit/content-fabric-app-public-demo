<?php

namespace App\Models\Billing;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $table = 'billing_transactions';

    protected $casts = [
        'amount' => 'float',
    ];

    protected $fillable = [
        'user_id',
        'team_id',
        'type',
        'direction',
        'amount',
        'currency',
        'description',
        'payment_id',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function delete()
    {
        return false;
    }
}
