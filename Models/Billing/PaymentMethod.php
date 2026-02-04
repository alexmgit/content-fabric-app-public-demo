<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use SoftDeletes;

    protected $table = 'billing_payment_methods';

    protected $fillable = [
        'user_id',
        'team_id',
        'is_default',
        'is_active',
        'payment_method_id',
        'payment_method_title',
        'payment_method_data',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
