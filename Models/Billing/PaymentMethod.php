<?php

namespace App\Models\Billing;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use SoftDeletes;

    protected $table = 'billing_payment_methods';

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'payment_method_data' => 'array',
    ];

    protected $fillable = [
        'user_id',
        'team_id',
        'is_default',
        'is_active',
        'payment_method_id',
        'payment_method_title',
        'payment_method_data',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
