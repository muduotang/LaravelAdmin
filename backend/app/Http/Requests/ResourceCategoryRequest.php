<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResourceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:200'],
            'sort' => ['required', 'integer', 'min:0'],
        ];

        // 更新时验证
        if ($this->isMethod('PUT')) {
            $rules['name'][] = Rule::unique('resource_categories')->ignore($this->route('resourceCategory'));
        } else {
            $rules['name'][] = 'unique:resource_categories';
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'name' => '分类名称',
            'sort' => '排序',
        ];
    }
} 