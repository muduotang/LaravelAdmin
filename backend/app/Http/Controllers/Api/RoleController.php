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
     * @param Request $request
     * @param Role $role
     * @return JsonResponse
     */
    public function assignMenus(Request $request, Role $role): JsonResponse
    {
        $this->validate($request, [
            'menu_ids' => 'required|array',
            'menu_ids.*' => 'required|integer|exists:menus,id',
        ]);

        $role->menus()->sync($request->input('menu_ids'));
        return $this->success();
    }

    /**
     * 分配资源给角色
     *
     * @param Request $request
     * @param Role $role
     * @return JsonResponse
     */
    public function assignResources(Request $request, Role $role): JsonResponse
    {
        $this->validate($request, [
            'resource_ids' => 'required|array',
            'resource_ids.*' => 'required|integer|exists:resources,id',
        ]);

        $role->resources()->sync($request->input('resource_ids'));
        return $this->success();
    }
}
