<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreGuestBorrowingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maximumItems = max(
            1,
            (int) Setting::getValue('max_items_per_borrowing', 10)
        );

        return [
            'role' => ['required', Rule::in(['student', 'professor', 'faculty_staff'])],
            'full_name' => ['required', 'string', 'max:180'],
            'id_number' => [
                'nullable',
                'required_if:role,student,faculty_staff',
                'string',
                'max:40',
            ],
            'email' => ['required', 'email', 'max:255'],
            'room' => ['required', 'string', 'max:120'],
            'program' => ['nullable', 'required_if:role,student', 'string', 'max:180'],
            'year_level' => ['nullable', 'required_if:role,student', 'string', 'max:40'],
            'section' => ['nullable', 'required_if:role,student', 'string', 'max:80'],
            'department' => ['nullable', 'required_if:role,professor', 'string', 'max:180'],
            'purpose' => ['required', 'string', 'max:1500'],
            'borrow_at' => ['required', 'date', 'after_or_equal:now'],
            'expected_return_at' => ['required', 'date', 'after:borrow_at'],
            'request_notes' => ['nullable', 'string', 'max:1500'],
            'item_unit_ids' => ['required', 'array', 'min:1', 'max:'.$maximumItems],
            'item_unit_ids.*' => ['integer', 'distinct', 'exists:item_units,id'],
            'terms_accepted' => ['accepted'],
            'privacy_accepted' => ['accepted'],
            'liability_accepted' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'terms_accepted.accepted' => 'You must accept the Terms and Conditions.',
            'privacy_accepted.accepted' => 'You must acknowledge the Privacy Policy.',
            'liability_accepted.accepted' => 'You must accept responsibility for lost or damaged equipment.',
            'id_number.required_if' => 'The ID number is required for the selected borrower role.',
            'room.required' => 'Please enter the room where the equipment will be used or delivered.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->filled('borrow_at') || ! $this->filled('expected_return_at')) {
                return;
            }

            try {
                $borrowAt = Carbon::parse($this->input('borrow_at'));
                $expectedReturnAt = Carbon::parse($this->input('expected_return_at'));
            } catch (\Throwable) {
                return;
            }

            $maximumDays = max(1, (int) Setting::getValue('max_borrow_days', 7));

            if ($borrowAt->diffInMinutes($expectedReturnAt) > $maximumDays * 1440) {
                $validator->errors()->add(
                    'expected_return_at',
                    "The borrowing period cannot exceed {$maximumDays} day(s)."
                );
            }

            $weekendAllowed = (bool) Setting::getValue('allow_weekend_borrowing', false);

            if (! $weekendAllowed && ($borrowAt->isWeekend() || $expectedReturnAt->isWeekend())) {
                $validator->errors()->add(
                    'borrow_at',
                    'Weekend borrowing is currently disabled in system settings.'
                );
            }
        });
    }
}
