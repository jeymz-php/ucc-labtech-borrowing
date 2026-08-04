<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CampusAccess
{
    private const FALLBACK_OPTIONS = [
        'Main Campus',
        'Congressional Extension Campus',
        'Camarin Extension Campus',
        'Bagong Silang Extension Campus',
    ];

    public static function options(): array
    {
        $configured = config('campuses.options');

        if (! is_array($configured) || $configured === []) {
            $legacy = config('campuses');

            if (is_array($legacy)) {
                $configured = isset($legacy['options']) && is_array($legacy['options'])
                    ? $legacy['options']
                    : array_filter(
                        $legacy,
                        fn ($value, $key) => $key !== 'default' && is_string($value),
                        ARRAY_FILTER_USE_BOTH
                    );
            }
        }

        if (! is_array($configured) || $configured === []) {
            $configured = self::FALLBACK_OPTIONS;
        }

        $options = collect($configured)
            ->map(fn ($value) => is_string($value) ? trim($value) : null)
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $options !== [] ? $options : self::FALLBACK_OPTIONS;
    }

    public static function default(): string
    {
        return self::normalize((string) config('campuses.default', 'Main Campus'))
            ?? 'Main Campus';
    }

    public static function normalize(?string $campus): ?string
    {
        if (! is_string($campus)) {
            return null;
        }

        $candidate = trim(preg_replace('/\s+/', ' ', $campus) ?? '');

        if ($candidate === '') {
            return null;
        }

        foreach (self::options() as $option) {
            if (strcasecmp($candidate, $option) === 0) {
                return $option;
            }

            if (Str::slug($candidate) === Str::slug($option)) {
                return $option;
            }
        }

        $aliases = [
            'main' => 'Main Campus',
            'congressional' => 'Congressional Extension Campus',
            'camaring' => 'Camarin Extension Campus',
            'camarin' => 'Camarin Extension Campus',
            'bagong-silang' => 'Bagong Silang Extension Campus',
            'bagong silang' => 'Bagong Silang Extension Campus',
        ];

        return $aliases[Str::lower($candidate)]
            ?? $aliases[Str::slug($candidate)]
            ?? null;
    }

    public static function isValid(?string $campus): bool
    {
        return self::normalize($campus) !== null;
    }

    public static function userCampus(User $user): string
    {
        return self::normalize((string) $user->campus)
            ?? self::default();
    }

    public static function canViewAllCampuses(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public static function canAccess(User $user, ?string $campus): bool
    {
        if (self::canViewAllCampuses($user)) {
            return true;
        }

        $normalized = self::normalize($campus);

        return $normalized !== null
            && hash_equals(self::userCampus($user), $normalized);
    }

    public static function ensureCanAccess(User $user, ?string $campus): void
    {
        abort_unless(
            self::canAccess($user, $campus),
            403,
            'This record belongs to another campus.'
        );
    }

    public static function campusForWrite(
        User $user,
        ?string $requestedCampus = null
    ): string {
        $normalized = self::normalize($requestedCampus);

        if (self::canViewAllCampuses($user) && $normalized !== null) {
            return $normalized;
        }

        return self::userCampus($user);
    }

    public static function validateRequestedCampus(?string $campus): string
    {
        $normalized = self::normalize($campus);

        if ($normalized === null) {
            throw ValidationException::withMessages([
                'campus' => 'Select a valid University of Caloocan City campus.',
            ]);
        }

        return $normalized;
    }

    public static function scopeForUser(
        Builder $query,
        User $user,
        string $column = 'campus'
    ): Builder {
        if (self::canViewAllCampuses($user)) {
            return $query;
        }

        return $query->where($column, self::userCampus($user));
    }
}
