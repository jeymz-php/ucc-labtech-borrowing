<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'user_code',
        'id_number',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'email',
        'password',
        'profile_picture',
        'contact_number',
        'campus',
        'department',
        'program',
        'year_level',
        'section',
        'account_status',
        'must_change_password',
        'temporary_password_sent_at',
        'terms_accepted_at',
        'privacy_policy_accepted_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'temporary_password_sent_at' => 'datetime',
        'terms_accepted_at' => 'datetime',
        'privacy_policy_accepted_at' => 'datetime',
        'must_change_password' => 'boolean',
    ];

    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ]);

        return implode(' ', $parts);
    }

    public function getInitialsAttribute(): string
    {
        return strtoupper(
            mb_substr($this->first_name, 0, 1) .
            mb_substr($this->last_name, 0, 1)
        );
    }

    public function getProfilePictureUrlAttribute(): ?string
    {
        if (
            ! $this->profile_picture ||
            ! Storage::disk('public')->exists($this->profile_picture)
        ) {
            return null;
        }

        return asset('storage/' . ltrim($this->profile_picture, '/'));
    }

    public function isActive(): bool
    {
        return $this->account_status === 'active';
    }

    public function borrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class);
    }

    public function createdItems(): HasMany
    {
        return $this->hasMany(
            Item::class,
            'created_by'
        );
    }

    public function createdItemUnits(): HasMany
    {
        return $this->hasMany(
            ItemUnit::class,
            'created_by'
        );
    }
}