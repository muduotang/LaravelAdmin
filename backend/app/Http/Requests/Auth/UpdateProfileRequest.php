<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'email' => 'nullable|email|max:255|unique:admins,email,' . $this->user('admin')->id,
            'nick_name' => 'nullable|string|max:50',
            'icon' => 'nullable|string|max:255',
        ];

        // 如果提供了旧密码，则验证密码相关字段
        if ($this->filled('old_password') || $this->filled('new_password')) {
            $rules['old_password'] = 'required|string';
            $rules['new_password'] = 'required|string|min:6|confirmed';
            $rules['new_password_confirmation'] = 'required|string';
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
            'email.email' => '邮箱格式不正确',
            'email.max' => '邮箱长度不能超过255个字符',
            'email.unique' => '该邮箱已被使用',
            'nick_name.max' => '昵称长度不能超过50个字符',
            'icon.max' => '头像地址长度不能超过255个字符',
            'old_password.required' => '原密码不能为空',
            'new_password.required' => '新密码不能为空',
            'new_password.min' => '新密码长度不能小于6个字符',
            'new_password.confirmed' => '两次输入的密码不一致',
            'new_password_confirmation.required' => '请确认新密码',
        ];
    }
} 