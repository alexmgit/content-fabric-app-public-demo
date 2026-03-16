<?php

namespace App\Models\Billing;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravelcm\Subscriptions\Models\Subscription;

class SubscriptionPayment extends Model
{
    use SoftDeletes;

    protected $table = 'billing_subscription_payments';

    protected $casts = [
        'subscription_starts_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'amount' => 'float',
        'retry_count' => 'integer',
    ];

    protected $fillable = [
        'user_id',
        'team_id',
        'payment_id',
        'subscription_id',
        'subscription_starts_at',
        'subscription_ends_at',
        'status',
        'amount',
        'retry_count',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
