<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreItemUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create items') ?? false;
    }

    public function rules(): array
    {
        return [
            'serial_number' => ['nullable','string','max:100',Rule::unique('item_units','serial_number')->whereNull('deleted_at')],
            'property_number' => ['nullable','string','max:100',Rule::unique('item_units','property_number')->whereNull('deleted_at')],
            'acquisition_date' => ['nullable','date','before_or_equal:today'],
            'acquisition_cost' => ['nullable','numeric','min:0','max:9999999999.99'],
            'location' => ['nullable','string','max:150'],
            'condition' => ['required',Rule::in(['excellent','good','fair','damaged','for_repair','unserviceable'])],
            'availability_status' => ['required',Rule::in(['available','reserved','borrowed','maintenance','lost'])],
            'remarks' => ['nullable','string','max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'serial_number' => $this->filled('serial_number') ? strtoupper(trim((string) $this->serial_number)) : null,
            'property_number' => $this->filled('property_number') ? strtoupper(trim((string) $this->property_number)) : null,
            'location' => $this->filled('location') ? trim((string) $this->location) : null,
        ]);
    }
}
