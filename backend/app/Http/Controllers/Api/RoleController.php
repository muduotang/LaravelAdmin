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
     * @param Request $request
     * @param Role $role
     * @return JsonResponse
     */
    public function assignMenus(Request $request, Role $role): JsonResponse
    {
        // 自定义验证逻辑
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'menu_ids' => 'present|array'
        ]);
        
        // 检查menu_ids中的ID是否有效
        if ($request->has('menu_ids')) {
            $menuIds = $request->menu_ids;
            if (!empty($menuIds)) {
                $existingMenuIds = \App\Models\Menu::whereIn('id', $menuIds)->pluck('id')->toArray();
                $invalidMenuIds = array_diff($menuIds, $existingMenuIds);
                
                if (!empty($invalidMenuIds)) {
                    $validator->errors()->add('menu_ids', 'The selected menu ids is invalid.');
                }
            }
        }
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => 'Validation failed.',
                'data' => null,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $this->roleService->assignMenus(
                $role,
                $request->input('menu_ids', []),
                auth('admin')->id(),
                $request->ip(),
                $request->userAgent()
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => 'Validation failed.',
                'data' => null,
                'errors' => [
                    'menu_ids' => [$e->getMessage()]
                ]
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'code' => 500,
                'message' => 'Server error.',
                'data' => null,
                'errors' => [
                    'message' => $e->getMessage()
                ]
            ], 500);
        }

        return $this->success(null, '菜单分配成功');
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
        // 自定义验证逻辑
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'resource_ids' => 'present|array'
        ]);
        
        // 检查resource_ids中的ID是否有效
        if ($request->has('resource_ids')) {
            $resourceIds = $request->resource_ids;
            if (!empty($resourceIds)) {
                $existingResourceIds = \App\Models\Resource::whereIn('id', $resourceIds)->pluck('id')->toArray();
                $invalidResourceIds = array_diff($resourceIds, $existingResourceIds);
                
                if (!empty($invalidResourceIds)) {
                    $validator->errors()->add('resource_ids', 'The selected resource ids is invalid.');
                }
            }
        }
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => 'Validation failed.',
                'data' => null,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $this->roleService->assignResources(
                $role,
                $request->input('resource_ids', []),
                auth('admin')->id(),
                $request->ip(),
                $request->userAgent()
            );

            return $this->success(null, '资源分配成功');
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => 'Validation failed.',
                'data' => null,
                'errors' => ['resource_ids' => [$e->getMessage()]]
            ], 422);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
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
