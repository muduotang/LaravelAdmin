<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ResourceCategoryRequest;
use App\Models\ResourceCategory;
use App\Services\ResourceCategoryService;
use Illuminate\Http\JsonResponse;

class ResourceCategoryController extends BaseController
{
    protected ResourceCategoryService $resourceCategoryService;

    public function __construct(ResourceCategoryService $resourceCategoryService)
    {
        $this->resourceCategoryService = $resourceCategoryService;
    }

    /**
     * 获取资源分类列表
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $categories = $this->resourceCategoryService->getList();
        return $this->success($categories);
    }

    /**
     * 创建资源分类
     *
     * @param ResourceCategoryRequest $request
     * @return JsonResponse
     */
    public function store(ResourceCategoryRequest $request): JsonResponse
    {
        $category = $this->resourceCategoryService->create($request->validated());
        return $this->success($category);
    }

    /**
     * 更新资源分类
     *
     * @param ResourceCategoryRequest $request
     * @param ResourceCategory $resourceCategory
     * @return JsonResponse
     */
    public function update(ResourceCategoryRequest $request, ResourceCategory $resourceCategory): JsonResponse
    {
        $category = $this->resourceCategoryService->update($resourceCategory, $request->validated());
        return $this->success($category);
    }

    /**
     * 删除资源分类
     *
     * @param ResourceCategory $resourceCategory
     * @return JsonResponse
     */
    public function destroy(ResourceCategory $resourceCategory): JsonResponse
    {
        $this->resourceCategoryService->delete($resourceCategory);
        return $this->success();
    }
} 