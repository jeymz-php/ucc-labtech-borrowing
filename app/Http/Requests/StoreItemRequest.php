<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create items') ?? false;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(function ($query) {
                    $query->where('status', 'active')->whereNull('deleted_at');
                }),
            ],
            'name' => ['required', 'string', 'max:150'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'minimum_stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'location' => ['nullable', 'string', 'max:150'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'initial_units_count' => ['required', 'integer', 'min:0', 'max:500'],
            'initial_condition' => [
                Rule::requiredIf(fn () => (int) $this->input('initial_units_count', 0) > 0),
                Rule::in(['excellent', 'good', 'fair', 'damaged', 'for_repair', 'unserviceable']),
            ],
            'acquisition_date' => ['nullable', 'date', 'before_or_equal:today'],
            'acquisition_cost' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'unit_remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'Select an active inventory category.',
            'image.max' => 'The item image must not exceed 4 MB.',
            'initial_units_count.max' => 'You may create a maximum of 500 units at a time.',
            'initial_condition.required_if' => 'Select the initial condition for the generated units.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->name),
            'brand' => $this->filled('brand') ? trim((string) $this->brand) : null,
            'model' => $this->filled('model') ? trim((string) $this->model) : null,
            'location' => $this->filled('location') ? trim((string) $this->location) : null,
            'initial_units_count' => $this->input('initial_units_count', 0),
        ]);
    }
}
