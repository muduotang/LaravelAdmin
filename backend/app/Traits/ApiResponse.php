<?php

namespace App\Traits;

use App\Http\Resources\BaseResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

trait ApiResponse
{
    /**
     * 成功响应
     *
     * @param mixed $data
     * @param string|null $message
     * @param int $code
     * @return JsonResponse
     */
    protected function success($data = null, string $message = null, int $code = BaseResource::HTTP_OK): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'code' => $code,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * 错误响应
     *
     * @param string $message
     * @param int $code
     * @param mixed $data
     * @return JsonResponse
     */
    protected function error(string $message, int $code = BaseResource::HTTP_ERROR, $data = null): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'code' => $code,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * 分页响应
     *
     * @param LengthAwarePaginator $paginator
     * @param string|null $message
     * @param int $code
     * @return JsonResponse
     */
    protected function paginate(LengthAwarePaginator $paginator, string $message = null, int $code = BaseResource::HTTP_OK): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'code' => $code,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage()
            ]
        ], $code);
    }

    /**
     * 集合响应
     *
     * @param Collection $collection
     * @param string|null $message
     * @param int $code
     * @return JsonResponse
     */
    protected function collection(Collection $collection, string $message = null, int $code = BaseResource::HTTP_OK): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'code' => $code,
            'message' => $message,
            'data' => $collection
        ], $code);
    }

    /**
     * 创建成功响应
     *
     * @param mixed $data
     * @param string|null $message
     * @return JsonResponse
     */
    protected function created($data = null, string $message = 'Created successfully'): JsonResponse
    {
        return $this->success($data, $message, BaseResource::HTTP_OK);
    }

    /**
     * 更新成功响应
     *
     * @param mixed $data
     * @param string|null $message
     * @return JsonResponse
     */
    protected function updated($data = null, string $message = 'Updated successfully'): JsonResponse
    {
        return $this->success($data, $message, BaseResource::HTTP_OK);
    }

    /**
     * 删除成功响应
     *
     * @param mixed $data
     * @param string|null $message
     * @return JsonResponse
     */
    protected function deleted($data = null, string $message = 'Deleted successfully'): JsonResponse
    {
        return $this->success($data, $message, BaseResource::HTTP_OK);
    }
} 