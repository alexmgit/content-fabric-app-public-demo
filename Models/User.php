<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use App\Models\Apify\Job;
use App\Models\Source\Source;
use App\Models\Search\Search;
use Laravelcm\Subscriptions\Traits\HasPlanSubscriptions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Billing\Transaction;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasPlanSubscriptions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'partner_id',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
        'telegram_hash',
        'spent_this_month',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function partner(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'partner_id');
    }

    public function telegram(): HasOne
    {
        return $this->hasOne(UserTelegram::class);
    }

    public function isAdmin(): bool
    {
        return in_array($this->id, config('services.admin_ids'));
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }

    public function canAccessLogViewer(): bool
    {
        return $this->isAdmin();
    }

    public function canAccessTelescope(): bool
    {
        return $this->isAdmin();
    }

    public function hasNewEvents(): bool
    {
        return true;
    }

    public function getSourceCountAttribute()
    {
        $value = Source::where('user_id', $this->id)->count();

        return $value;
    }

    public function getSearchCountAttribute()
    {
        $value = Search::where('user_id', $this->id)->count();

        return $value;
    }

    public function getSpentTotalAttribute()
    {
        $spent = Job::where('user_id', $this->id)->sum('price');

        return round($spent, 2);
    }

    public function getSpentThisMonthAttribute()
    {
        $spent = Job::where('user_id', $this->id)
            ->where('created_at', '>=', now()->startOfMonth())
            ->where('created_at', '<=', now()->endOfMonth())
            ->sum('price');

        return round($spent, 2);
    }   

    public function getTelegramHashAttribute(): string
    {
        return sha1($this->id . config('services.telegram_notifications.bot_token'));
    }

    public function getBalanceAttribute()
    {
        return round(Transaction::where('user_id', $this->id)->sum('amount'), 2);
    }

    public function getAllowTrialAttribute()
    {
        return $this->planSubscriptions()->withTrashed()->count() === 0;
    }
}
