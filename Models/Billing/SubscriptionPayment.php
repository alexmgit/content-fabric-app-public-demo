<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravelcm\Subscriptions\Models\Subscription;

class SubscriptionPayment extends Model
{
    use SoftDeletes;

    protected $table = 'billing_subscription_payments';

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

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
