<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;

class ExampleController extends BaseController
{
    /**
     * V2版本的成功响应示例
     */
    public function successExample()
    {
        $data = [
            'name' => 'example-v2',
            'description' => 'This is a V2 success response example',
            'version' => 'v2',
            'features' => [
                'version_control',
                'unified_response',
                'extended_metadata'
            ]
        ];

        return $this->success($data, 'Data retrieved successfully from V2 API');
    }
} 