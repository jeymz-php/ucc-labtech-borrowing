<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkStoreItemUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create items') ?? false;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required','integer','min:1','max:250'],
            'acquisition_date' => ['nullable','date','before_or_equal:today'],
            'acquisition_cost' => ['nullable','numeric','min:0','max:9999999999.99'],
            'location' => ['nullable','string','max:150'],
            'condition' => ['required',Rule::in(['excellent','good','fair','damaged','for_repair','unserviceable'])],
            'availability_status' => ['required',Rule::in(['available','maintenance'])],
            'remarks' => ['nullable','string','max:2000'],
        ];
    }
}
