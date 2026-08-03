<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuestBorrower extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_code',
        'role',
        'full_name',
        'id_number',
        'email',
        'program',
        'year_level',
        'section',
        'department',
    ];

    public function borrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class);
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'student' => 'Student',
            'professor' => 'Professor',
            'faculty_staff' => 'Faculty / Staff',
            default => ucfirst(str_replace('_', ' ', $this->role)),
        };
    }

    public function getAcademicDetailsAttribute(): ?string
    {
        if ($this->role === 'student') {
            return collect([$this->program, $this->year_level, $this->section])
                ->filter()
                ->implode(' · ') ?: null;
        }

        return $this->department;
    }
}
