<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource
{
    /**
     * 成功状态码
     */
    const HTTP_OK = 200;

    /**
     * 错误状态码
     */
    const HTTP_ERROR = 400;

    /**
     * 未授权状态码
     */
    const HTTP_UNAUTHORIZED = 401;

    /**
     * 禁止访问状态码
     */
    const HTTP_FORBIDDEN = 403;

    /**
     * 资源不存在状态码
     */
    const HTTP_NOT_FOUND = 404;

    /**
     * 服务器错误状态码
     */
    const HTTP_SERVER_ERROR = 500;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }

    /**
     * 自定义资源集合的包装
     */
    public static function collection($resource)
    {
        return parent::collection($resource)->additional([
            'status' => 'success',
            'code' => self::HTTP_OK,
            'message' => null
        ]);
    }

    /**
     * 自定义资源的包装
     */
    public function additional($data)
    {
        return array_merge([
            'status' => 'success',
            'code' => self::HTTP_OK,
            'message' => null
        ], $data);
    }

    /**
     * 自定义资源的错误响应
     */
    public static function error($message, $code = self::HTTP_ERROR, $data = null)
    {
        return response()->json([
            'status' => 'error',
            'code' => $code,
            'message' => $message,
            'data' => $data
        ], $code);
    }
}
