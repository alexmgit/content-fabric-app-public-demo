<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Transaction extends Model
{
    //
    protected $table = 'billing_transactions';

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

    public function payment()
    {
        return $this->belongsTo(Payment::class);
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
