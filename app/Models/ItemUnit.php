<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemUnit extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'item_id',
        'campus',
        'asset_number',
        'barcode_value',
        'barcode_path',
        'serial_number',
        'property_number',
        'acquisition_date',
        'acquisition_cost',
        'location',
        'condition',
        'availability_status',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'acquisition_cost' => 'decimal:2',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function borrowingItems(): HasMany
    {
        return $this->hasMany(BorrowingItem::class);
    }

    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }


    public function scopeForCampus(Builder $query, ?string $campus): Builder
    {
        return $campus ? $query->where('campus', $campus) : $query;
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->hasRole('super_admin')
            ? $query
            : $query->where('campus', $user->campus);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where(
            'availability_status',
            'available'
        );
    }

    public function scopeBorrowable(Builder $query): Builder
    {
        return $query
            ->where('availability_status', 'available')
            ->whereIn('condition', [
                'excellent',
                'good',
                'fair',
            ]);
    }

    public function isBorrowable(): bool
    {
        return $this->availability_status === 'available'
            && in_array($this->condition, [
                'excellent',
                'good',
                'fair',
            ], true);
    }
}