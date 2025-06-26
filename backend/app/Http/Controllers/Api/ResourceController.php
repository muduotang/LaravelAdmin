<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ResourceRequest;
use App\Models\Resource;
use App\Services\ResourceService;
use Illuminate\Http\JsonResponse;

class ResourceController extends BaseController
{
    protected ResourceService $resourceService;

    public function __construct(ResourceService $resourceService)
    {
        $this->resourceService = $resourceService;
    }

    /**
     * 获取资源列表
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $resources = $this->resourceService->getList();
        return $this->success($resources);
    }

    /**
     * 创建资源
     *
     * @param ResourceRequest $request
     * @return JsonResponse
     */
    public function store(ResourceRequest $request): JsonResponse
    {
        $resource = $this->resourceService->create($request->validated());
        return $this->success($resource);
    }

    /**
     * 更新资源
     *
     * @param ResourceRequest $request
     * @param Resource $resource
     * @return JsonResponse
     */
    public function update(ResourceRequest $request, Resource $resource): JsonResponse
    {
        $resource = $this->resourceService->update($resource, $request->validated());
        return $this->success($resource);
    }

    /**
     * 删除资源
     *
     * @param Resource $resource
     * @return JsonResponse
     */
    public function destroy(Resource $resource): JsonResponse
    {
        $this->resourceService->delete($resource);
        return $this->success();
    }
} 