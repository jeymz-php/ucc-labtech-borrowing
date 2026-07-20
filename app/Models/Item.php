<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Item extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'item_code',
        'category_id',
        'name',
        'brand',
        'model',
        'description',
        'image',
        'quantity_total',
        'quantity_available',
        'minimum_stock',
        'location',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'quantity_total' => 'integer',
        'quantity_available' => 'integer',
        'minimum_stock' => 'integer',
    ];


    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image || ! Storage::disk('public')->exists($this->image)) {
            return null;
        }

        return asset('storage/' . ltrim($this->image, '/'));
    }

    public function getDisplayNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->brand,
            $this->name,
            $this->model,
        ])));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(ItemUnit::class);
    }

    public function availableUnits(): HasMany
    {
        return $this->hasMany(ItemUnit::class)
            ->where('availability_status', 'available');
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {
        if (!$search) {
            return $query;
        }

        return $query->where(function (Builder $subQuery) use ($search) {
            $subQuery
                ->where('item_code', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('brand', 'like', "%{$search}%")
                ->orWhere('model', 'like', "%{$search}%");
        });
    }

    public function isLowStock(): bool
    {
        return $this->quantity_available <= $this->minimum_stock;
    }

    public function refreshQuantities(): void
    {
        $this->update([
            'quantity_total' => $this->units()
                ->where('availability_status', '!=', 'archived')
                ->count(),

            'quantity_available' => $this->units()
                ->where('availability_status', 'available')
                ->count(),
        ]);
    }
}