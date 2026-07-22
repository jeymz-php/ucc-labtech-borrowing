<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBorrowingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create borrowing requests') ?? false;
    }

    public function rules(): array
    {
        $maximumItems = max(
            1,
            (int) Setting::getValue('max_items_per_borrowing', 10)
        );

        return [
            'purpose' => ['required', 'string', 'max:1500'],
            'borrow_at' => ['required', 'date', 'after_or_equal:today'],
            'expected_return_at' => ['required', 'date', 'after:borrow_at'],
            'request_notes' => ['nullable', 'string', 'max:1500'],
            'item_unit_ids' => [
                'required',
                'array',
                'min:1',
                'max:'.$maximumItems,
            ],
            'item_unit_ids.*' => [
                'integer',
                'distinct',
                'exists:item_units,id',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (
                ! $this->filled('borrow_at')
                || ! $this->filled('expected_return_at')
            ) {
                return;
            }

            try {
                $borrowAt = Carbon::parse($this->input('borrow_at'));
                $expectedReturnAt = Carbon::parse(
                    $this->input('expected_return_at')
                );
            } catch (\Throwable) {
                return;
            }

            $maximumDays = max(
                1,
                (int) Setting::getValue('max_borrow_days', 7)
            );

            if ($borrowAt->diffInMinutes($expectedReturnAt) > $maximumDays * 1440) {
                $validator->errors()->add(
                    'expected_return_at',
                    "The borrowing period cannot exceed {$maximumDays} day(s)."
                );
            }

            $weekendAllowed = (bool) Setting::getValue(
                'allow_weekend_borrowing',
                false
            );

            if (
                ! $weekendAllowed
                && ($borrowAt->isWeekend() || $expectedReturnAt->isWeekend())
            ) {
                $validator->errors()->add(
                    'borrow_at',
                    'Weekend borrowing is currently disabled in system settings.'
                );
            }
        });
    }
}
