<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
        ];

        // 创建时检查唯一性
        if ($this->isMethod('post')) {
            $rules['name'] .= '|unique:roles';
        }

        // 更新时检查唯一性（排除当前记录）
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['name'] .= '|unique:roles,name,' . $this->route('role')->id;
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => '角色名称不能为空',
            'name.max' => '角色名称不能超过50个字符',
            'name.unique' => '角色名称已存在',
            'description.max' => '角色描述不能超过255个字符',
        ];
    }
} 