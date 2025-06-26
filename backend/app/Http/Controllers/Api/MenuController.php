<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\MenuRequest;
use App\Models\Menu;
use App\Services\MenuService;
use Illuminate\Http\JsonResponse;

class MenuController extends BaseController
{
    protected MenuService $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    /**
     * 获取菜单树形结构
     *
     * @return JsonResponse
     */
    public function tree(): JsonResponse
    {
        $tree = $this->menuService->getMenuTree();
        return $this->success($tree);
    }

    /**
     * 获取菜单列表
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $menus = $this->menuService->getMenuList();
        return $this->success($menus);
    }

    /**
     * 创建菜单
     *
     * @param MenuRequest $request
     * @return JsonResponse
     */
    public function store(MenuRequest $request): JsonResponse
    {
        $menu = $this->menuService->createMenu($request->validated());
        return $this->success($menu);
    }

    /**
     * 更新菜单
     *
     * @param MenuRequest $request
     * @param Menu $menu
     * @return JsonResponse
     */
    public function update(MenuRequest $request, Menu $menu): JsonResponse
    {
        $menu = $this->menuService->updateMenu($menu, $request->validated());
        return $this->success($menu);
    }

    /**
     * 删除菜单
     *
     * @param Menu $menu
     * @return JsonResponse
     */
    public function destroy(Menu $menu): JsonResponse
    {
        $this->menuService->deleteMenu($menu);
        return $this->success();
    }
} 