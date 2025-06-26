<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $adminId = $this->route('admin')?->id;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        $rules = [
            'username' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9_]+$/',
                Rule::unique('admins')->ignore($adminId)
            ],
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('admins')->ignore($adminId)
            ],
            'nick_name' => 'required|string|max:50',
            'note' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:500',
            'status' => 'required|in:0,1',
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:roles,id'
        ];

        // 创建时密码必填，更新时密码可选
        if (!$isUpdate) {
            $rules['password'] = 'required|string|min:6|confirmed';
        } else {
            $rules['password'] = 'nullable|string|min:6|confirmed';
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
            'username.required' => '用户名不能为空',
            'username.unique' => '用户名已存在',
            'username.regex' => '用户名只能包含字母、数字和下划线',
            'username.max' => '用户名不能超过50个字符',
            'email.required' => '邮箱不能为空',
            'email.email' => '邮箱格式不正确',
            'email.unique' => '邮箱已存在',
            'email.max' => '邮箱不能超过100个字符',
            'nick_name.required' => '昵称不能为空',
            'nick_name.max' => '昵称不能超过50个字符',
            'note.max' => '备注不能超过500个字符',
            'icon.max' => '头像地址不能超过500个字符',
            'status.required' => '状态不能为空',
            'status.in' => '状态值无效',
            'password.required' => '密码不能为空',
            'password.min' => '密码至少6位',
            'password.confirmed' => '两次密码输入不一致',
            'role_ids.array' => '角色ID必须是数组',
            'role_ids.*.exists' => '选择的角色不存在'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'username' => '用户名',
            'email' => '邮箱',
            'nick_name' => '昵称',
            'note' => '备注',
            'icon' => '头像',
            'status' => '状态',
            'password' => '密码',
            'role_ids' => '角色'
        ];
    }
}