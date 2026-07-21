<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBorrowingRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('create borrowing requests') ?? false; }

    public function rules(): array
    {
        return [
            'purpose' => ['required','string','max:1500'],
            'borrow_at' => ['required','date','after_or_equal:today'],
            'expected_return_at' => ['required','date','after:borrow_at'],
            'request_notes' => ['nullable','string','max:1500'],
            'item_unit_ids' => ['required','array','min:1','max:10'],
            'item_unit_ids.*' => ['integer','distinct','exists:item_units,id'],
        ];
    }
}
