<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\AdminRequest;
use App\Http\Resources\AdminResource;
use App\Models\Admin;
use App\Services\AdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends BaseController
{
    protected AdminService $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    /**
     * 获取管理员列表
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $keyword = $request->get('keyword');
        $status = $request->get('status');
        $roleId = $request->get('role_id');

        $admins = $this->adminService->getAdminList($perPage, $keyword, $status, $roleId);

        return $this->success([
            'data' => AdminResource::collection($admins->items()),
            'current_page' => $admins->currentPage(),
            'per_page' => $admins->perPage(),
            'total' => $admins->total(),
            'last_page' => $admins->lastPage(),
        ], '获取管理员列表成功');
    }

    /**
     * 创建管理员
     *
     * @param AdminRequest $request
     * @return JsonResponse
     */
    public function store(AdminRequest $request): JsonResponse
    {
        $admin = $this->adminService->createAdmin($request->validated());

        return $this->success(new AdminResource($admin), '创建管理员成功', 201);
    }

    /**
     * 获取管理员详情
     *
     * @param Admin $admin
     * @return JsonResponse
     */
    public function show(Admin $admin): JsonResponse
    {
        $admin->load('roles');

        return $this->success(new AdminResource($admin), '获取管理员详情成功');
    }

    /**
     * 更新管理员
     *
     * @param AdminRequest $request
     * @param Admin $admin
     * @return JsonResponse
     */
    public function update(AdminRequest $request, Admin $admin): JsonResponse
    {
        $updatedAdmin = $this->adminService->updateAdmin($admin, $request->validated());

        return $this->success(new AdminResource($updatedAdmin), '更新管理员成功');
    }

    /**
     * 删除管理员
     *
     * @param Admin $admin
     * @return JsonResponse
     */
    public function destroy(Admin $admin): JsonResponse
    {
        $this->adminService->deleteAdmin($admin);

        return $this->success(null, '删除管理员成功');
    }

    /**
     * 分配角色
     *
     * @param Request $request
     * @param Admin $admin
     * @return JsonResponse
     */
    public function assignRoles(Request $request, Admin $admin): JsonResponse
    {
        // 验证基本规则
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'role_ids' => 'present|array'
        ]);
        
        // 检查角色ID是否存在
        if (!$validator->fails()) {
            $roleIds = $request->role_ids ?? [];
            if (!empty($roleIds)) {
                // 确保类型一致性
                $roleIds = array_map('intval', $roleIds);
                $existingRoleIds = \App\Models\Role::whereIn('id', $roleIds)->pluck('id')->map(function($id) {
                    return (int) $id;
                })->toArray();
                $invalidRoleIds = array_diff($roleIds, $existingRoleIds);
                
                if (!empty($invalidRoleIds)) {
                    $validator->errors()->add('role_ids', 'The selected role ids is invalid.');
                }
            }
        }
        
        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $this->adminService->assignRoles(
                $admin,
                $request->role_ids ?? [],
                auth()->id(),
                $request->ip(),
                $request->userAgent()
            );
            
            return $this->success(null, '分配角色成功');
        } catch (\InvalidArgumentException $e) {
            // 捕获角色ID验证失败的异常
            $validator->errors()->add('role_ids', $e->getMessage());
            
            return response()->json([
                'code' => 422,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $validator->errors()
            ], 422);
        }
    }

    /**
     * 重置密码
     *
     * @param Request $request
     * @param Admin $admin
     * @return JsonResponse
     */
    public function resetPassword(Request $request, Admin $admin): JsonResponse
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed'
        ]);

        $admin->update([
            'password' => Hash::make($request->password)
        ]);

        return $this->success(null, '重置密码成功');
    }

    /**
     * 更新状态
     *
     * @param Request $request
     * @param Admin $admin
     * @return JsonResponse
     */
    public function updateStatus(Request $request, Admin $admin): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:0,1'
        ]);

        $this->adminService->updateStatus($admin, $request->status);

        return $this->success(null, '更新状态成功');
    }
}