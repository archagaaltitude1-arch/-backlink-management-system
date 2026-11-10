<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'account_type', 'role_id', 'status', 'phone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $attributes = ['status' => 'active', 'account_type' => 'customer'];

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if ($user->role_id === null) {
                $roleName = match ($user->account_type ?? 'customer') {
                    'admin' => 'Admin',
                    'staff' => 'Staff',
                    'seo' => 'SEO',
                    default => 'Customer',
                };
                $user->role()->associate(Role::query()->firstOrCreate(['role_name' => $roleName], ['permissions' => []]));
            }
        });
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class, 'customer_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function savedWebsites(): HasMany
    {
        return $this->hasMany(SavedWebsite::class, 'customer_id');
    }

    public function billingAddresses(): HasMany
    {
        return $this->hasMany(BillingAddress::class, 'customer_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'customer_id');
    }

    public function couponUsages(): HasMany
    {
        return $this->hasMany(CouponUsage::class, 'customer_id');
    }

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
            'role_id' => 'integer',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function isActiveAdmin(): bool
    {
        return $this->status === 'active' && $this->role?->role_name === 'Admin';
    }
}
