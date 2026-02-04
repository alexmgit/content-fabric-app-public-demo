<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Support\Str;

class Payment extends Model
{
    protected $table = 'billing_payments';

    protected $fillable = [
        'uuid',
        'user_id',
        'team_id',
        'amount',
        'currency',
        'description',
        'status',
        'payment_id',
        'confirmation_token',
        'extra_data',
    ];

    protected static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->uuid = Str::uuid();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function delete()
    {
        return false;
    }
}
