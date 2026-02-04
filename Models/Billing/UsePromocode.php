<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UsePromocode extends Model
{
    //
    protected $table = 'billing_use_promocode';

    protected $fillable = [
        'user_id',
        'team_id',
        'promocode',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function delete()
    {
        return false;
    }
}
