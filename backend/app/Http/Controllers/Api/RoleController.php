<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use App\Services\RoleService;
use App\Http\Requests\RoleRequest;
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
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $roles = $this->roleService->list($request->all());

        return $this->success([
            'items' => $roles->items(),
            'total' => $roles->total(),
            'current_page' => $roles->currentPage(),
            'per_page' => $roles->perPage(),
            'last_page' => $roles->lastPage(),
        ]);
    }

    /**
     * 创建角色
     *
     * @param RoleRequest $request
     * @return JsonResponse
     */
    public function store(RoleRequest $request): JsonResponse
    {
        $role = $this->roleService->create(
            $request->validated(),
            $request->user('admin')->id,
            $request->ip(),
            $request->userAgent()
        );

        return $this->success($role, '角色创建成功');
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
        $role = $this->roleService->update(
            $role,
            $request->validated(),
            $request->user('admin')->id,
            $request->ip(),
            $request->userAgent()
        );

        return $this->success($role, '角色更新成功');
    }

    /**
     * 删除角色
     *
     * @param Request $request
     * @param Role $role
     * @return JsonResponse
     */
    public function destroy(Request $request, Role $role): JsonResponse
    {
        $this->roleService->delete(
            $role,
            $request->user('admin')->id,
            $request->ip(),
            $request->userAgent()
        );

        return $this->success(null, '角色删除成功');
    }

    /**
     * 分配权限给角色
     *
     * @param Request $request
     * @param Role $role
     * @return JsonResponse
     */
    public function assignPermissions(Request $request, Role $role): JsonResponse
    {
        $this->validate($request, [
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'required|integer|exists:permissions,id',
        ]);

        $this->roleService->assignPermissions(
            $role,
            $request->input('permission_ids'),
            $request->ip(),
            $request->userAgent()
        );

        return $this->success(null, '权限分配成功');
    }

    /**
     * 获取角色的权限列表
     *
     * @param Role $role
     * @return JsonResponse
     */
    public function getPermissions(Role $role): JsonResponse
    {
        $permissions = $this->roleService->getPermissions($role);
        return $this->success($permissions);
    }
}
