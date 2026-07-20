<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit categories') ?? false;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')->id;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')
                    ->ignore($categoryId)
                    ->whereNull('deleted_at'),
            ],

            'asset_prefix' => [
                'required',
                'string',
                'min:2',
                'max:10',
                'regex:/^[A-Za-z0-9]+$/',
                Rule::unique('categories', 'asset_prefix')
                    ->ignore($categoryId)
                    ->whereNull('deleted_at'),
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'status' => [
                'required',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'asset_prefix.regex' =>
                'The asset prefix may only contain letters and numbers.',

            'asset_prefix.unique' =>
                'This asset prefix is already assigned to another category.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->name),
            'asset_prefix' => strtoupper(
                trim((string) $this->asset_prefix)
            ),
        ]);
    }
}