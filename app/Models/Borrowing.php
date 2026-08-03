<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Borrowing extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrowing_code',
        'user_id',
        'guest_borrower_id',
        'public_token',
        'source',
        'purpose',
        'borrow_at',
        'expected_return_at',
        'released_at',
        'returned_at',
        'status',
        'request_notes',
        'admin_notes',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'released_by',
        'received_by',
        'extended_by',
        'extended_at',
        'extension_reason',
        'terms_accepted_at',
        'privacy_accepted_at',
        'liability_accepted_at',
    ];

    protected $casts = [
        'borrow_at' => 'datetime',
        'expected_return_at' => 'datetime',
        'released_at' => 'datetime',
        'returned_at' => 'datetime',
        'approved_at' => 'datetime',
        'extended_at' => 'datetime',
        'terms_accepted_at' => 'datetime',
        'privacy_accepted_at' => 'datetime',
        'liability_accepted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guestBorrower(): BelongsTo
    {
        return $this->belongsTo(GuestBorrower::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BorrowingItem::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function releaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function extender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'extended_by');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->can('view all borrowings')
            ? $query
            : $query->where('user_id', $user->id);
    }

    public function canBeCancelledBy(User $user): bool
    {
        return in_array($this->status, ['pending', 'approved'], true)
            && ($this->user_id === $user->id || $user->can('cancel borrowings'));
    }

    public function getIsLateAttribute(): bool
    {
        return in_array($this->status, ['released', 'overdue'], true)
            && $this->expected_return_at?->isPast();
    }

    public function getIsGuestAttribute(): bool
    {
        return $this->source === 'guest' || $this->guest_borrower_id !== null;
    }

    public function getBorrowerNameAttribute(): string
    {
        return $this->guestBorrower?->full_name
            ?? $this->user?->full_name
            ?? 'Unknown borrower';
    }

    public function getBorrowerIdentifierAttribute(): ?string
    {
        return $this->guestBorrower?->id_number
            ?? $this->user?->id_number;
    }

    public function getBorrowerEmailAttribute(): ?string
    {
        return $this->guestBorrower?->email
            ?? $this->user?->email;
    }

    public function getBorrowerRoleLabelAttribute(): ?string
    {
        if ($this->guestBorrower) {
            return $this->guestBorrower->role_label;
        }

        $role = $this->user?->getRoleNames()->first();

        return $role ? ucwords(str_replace('_', ' ', $role)) : null;
    }
}
