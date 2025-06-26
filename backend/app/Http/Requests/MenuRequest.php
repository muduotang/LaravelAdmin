<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'parent_id' => ['nullable', 'exists:menus,id'],
            'title' => ['required', 'string', 'max:100'],
            'level' => ['required', 'integer', 'min:0'],
            'sort' => ['required', 'integer', 'min:0'],
            'name' => ['nullable', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:200'],
            'hidden' => ['required', 'boolean'],
            'keep_alive' => ['required', 'boolean'],
        ];

        // 更新时验证
        if ($this->isMethod('PUT')) {
            $rules['name'][] = Rule::unique('menus')->ignore($this->route('menu'));
        } else {
            $rules['name'][] = 'unique:menus';
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'parent_id' => '父级菜单',
            'title' => '菜单名称',
            'level' => '菜单级数',
            'sort' => '排序',
            'name' => '前端路由名称',
            'icon' => '图标',
            'hidden' => '是否隐藏',
            'keep_alive' => '是否缓存',
        ];
    }
} 