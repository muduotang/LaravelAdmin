<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class BaseResource extends JsonResource
{
    /**
     * HTTP 状态码
     */
    protected static $statusCodes;

    /**
     * 初始化状态码
     */
    public function __construct($resource)
    {
        parent::__construct($resource);
        self::$statusCodes = config('api.response.status_codes');
    }

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
     * 获取附加的元数据
     *
     * @return array
     */
    protected function getMetadata(): array
    {
        return [
            'api_version' => request()->get('api_version', config('api.versions.default')),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * 自定义资源集合的包装
     */
    public static function collection($resource)
    {
        return parent::collection($resource)->additional([
            'status' => 'success',
            'code' => self::$statusCodes['success'] ?? 200,
            'message' => null,
            'meta' => (new static(null))->getMetadata()
        ]);
    }

    /**
     * 自定义资源的包装
     */
    public function additional($data)
    {
        return array_merge([
            'status' => 'success',
            'code' => self::$statusCodes['success'] ?? 200,
            'message' => null,
            'meta' => $this->getMetadata()
        ], $data);
    }

    /**
     * 自定义资源的错误响应
     */
    public static function error($message, $type = 'bad_request', $data = null)
    {
        $code = self::$statusCodes[$type] ?? self::$statusCodes['bad_request'];
        
        return response()->json([
            'status' => 'error',
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'meta' => (new static(null))->getMetadata()
        ], $code);
    }

    /**
     * 自定义分页响应
     */
    public static function paginated($paginator, $message = null)
    {
        return response()->json([
            'status' => 'success',
            'code' => self::$statusCodes['success'] ?? 200,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => array_merge((new static(null))->getMetadata(), [
                'pagination' => [
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ]
            ])
        ]);
    }
}
