<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use App\Services\RoleService;
use App\Http\Requests\RoleRequest;
use App\Http\Requests\Role\AssignMenusRequest;
use App\Http\Requests\Role\AssignResourcesRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends BaseController
{
    /**
     * @var RoleService
     */
    protected $roleService;

    /**
     * RoleController constructor.
     *
     * @param RoleService $roleService
     */
    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * 获取角色列表
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $roles = Role::all();
        return $this->success($roles);
    }

    /**
     * 创建角色
     *
     * @param RoleRequest $request
     * @return JsonResponse
     */
    public function store(RoleRequest $request): JsonResponse
    {
        $role = Role::create($request->validated());
        return $this->success($role);
    }

    /**
     * 获取单个角色详情
     *
     * @param Role $role
     * @return JsonResponse
     */
    public function show(Role $role): JsonResponse
    {
        return $this->success($role);
    }

    /**
     * 更新角色
     *
     * @param RoleRequest $request
     * @param Role $role
     * @return JsonResponse
     */
    public function update(RoleRequest $request, Role $role): JsonResponse
    {
        $role->update($request->validated());
        return $this->success($role);
    }

    /**
     * 删除角色
     *
     * @param Role $role
     * @return JsonResponse
     */
    public function destroy(Role $role): JsonResponse
    {
        if ($role->admins()->exists()) {
            return $this->error('该角色下还有管理员，无法删除');
        }

        $role->delete();
        return $this->success();
    }

    /**
     * 分配菜单给角色
     *
     * @param AssignMenusRequest $request
     * @param Role $role
     * @return JsonResponse
     */
    public function assignMenus(AssignMenusRequest $request, Role $role): JsonResponse
    {
        $this->roleService->assignMenus(
            $role,
            $request->validated('menu_ids'),
            auth('admin')->id(),
            $request->ip(),
            $request->userAgent()
        );

        return $this->success(null, '菜单分配成功');
    }

    /**
     * 分配资源给角色
     *
     * @param AssignResourcesRequest $request
     * @param Role $role
     * @return JsonResponse
     */
    public function assignResources(AssignResourcesRequest $request, Role $role): JsonResponse
    {
        $this->roleService->assignResources(
            $role,
            $request->validated('resource_ids'),
            auth('admin')->id(),
            $request->ip(),
            $request->userAgent()
        );

        return $this->success(null, '资源分配成功');
    }

    /**
     * 获取角色的菜单
     *
     * @param Role $role
     * @return JsonResponse
     */
    public function getMenus(Role $role): JsonResponse
    {
        $menuIds = $this->roleService->getMenus($role);
        return $this->success($menuIds);
    }

    /**
     * 获取角色的资源
     *
     * @param Role $role
     * @return JsonResponse
     */
    public function getResources(Role $role): JsonResponse
    {
        $resourceIds = $this->roleService->getResources($role);
        return $this->success($resourceIds);
    }
}
