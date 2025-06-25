<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API 配置
    |--------------------------------------------------------------------------
    */

    // API 版本配置
    'versions' => [
        'default' => 'v1',
        'supported' => ['v1', 'v2'],
        'newest' => 'v1'
    ],

    // API 前缀配置
    'prefix' => [
        'global' => 'api',    // 全局前缀
        'version' => 'v1',    // 版本前缀
    ],

    // 响应格式配置
    'response' => [
        // 统一状态码
        'status_codes' => [
            'success' => 200,          // 成功
            'created' => 201,          // 创建成功
            'accepted' => 202,         // 请求已接受
            'no_content' => 204,       // 无内容
            'bad_request' => 400,      // 请求错误
            'unauthorized' => 401,      // 未授权
            'forbidden' => 403,        // 禁止访问
            'not_found' => 404,        // 未找到
            'method_not_allowed' => 405, // 方法不允许
            'unprocessable' => 422,    // 请求参数错误
            'too_many_requests' => 429, // 请求过多
            'server_error' => 500,     // 服务器错误
        ],

        // 默认分页配置
        'pagination' => [
            'default_per_page' => 15,
            'max_per_page' => 100,
        ],
    ],

    // 限流配置
    'throttle' => [
        'enabled' => true,
        'max_attempts' => 60,     // 最大请求次数
        'decay_minutes' => 1,     // 时间窗口（分钟）
    ],

    // 跨域配置
    'cors' => [
        'allowed_methods' => ['*'],
        'allowed_origins' => ['*'],
        'allowed_headers' => ['*'],
        'exposed_headers' => [],
        'max_age' => 0,
        'supports_credentials' => false,
    ],

    // 文档配置
    'documentation' => [
        'enabled' => true,
        'route' => '/api/documentation',
    ],
]; 