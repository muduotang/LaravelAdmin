<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class AssignResourcesRequest extends FormRequest
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
            'resource_ids' => ['present', 'array'],
            'resource_ids.*' => ['integer', 'exists:resources,id'],
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
            'resource_ids.present' => 'The resource ids field must be present.',
            'resource_ids.array' => 'The resource ids must be an array.',
            'resource_ids.*.integer' => 'Each resource id must be an integer.',
            'resource_ids.*.exists' => 'The selected resource does not exist.',
        ];
    }
} 