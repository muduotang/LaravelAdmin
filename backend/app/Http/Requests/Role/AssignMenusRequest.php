<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class AssignMenusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'menu_ids' => ['present', 'array'],
            'menu_ids.*' => ['integer', 'exists:menus,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'menu_ids.present' => 'The menu ids field must be present.',
            'menu_ids.array' => 'The menu ids must be an array.',
            'menu_ids.*.integer' => 'Each menu id must be an integer.',
            'menu_ids.*.exists' => 'The selected menu does not exist.',
        ];
    }
} 