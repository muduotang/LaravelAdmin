<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'category_id' => ['required', 'exists:resource_categories,id'],
            'name' => ['required', 'string', 'max:200'],
            'route_name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:500'],
        ];

        // 更新时验证
        if ($this->isMethod('PUT')) {
            $rules['route_name'][] = Rule::unique('resources')->ignore($this->route('resource'));
        } else {
            $rules['route_name'][] = 'unique:resources';
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'category_id' => '资源分类',
            'name' => '资源名称',
            'route_name' => '路由名称',
            'description' => '描述',
        ];
    }
} 