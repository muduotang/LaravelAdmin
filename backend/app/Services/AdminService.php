<?php

namespace App\Services;

use App\Models\Admin;
use App\Exceptions\AdminException;
use App\Traits\AdminOperationLoggable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminService
{
    use AdminOperationLoggable;
    /**
     * 获取管理员列表
     *
     * @param int $perPage
     * @param string|null $keyword
     * @param int|null $status
     * @param int|null $roleId
     * @return LengthAwarePaginator
     */
    public function getAdminList(int $perPage = 15, ?string $keyword = null, ?int $status = null, ?int $roleId = null): LengthAwarePaginator
    {
        $query = Admin::with('roles');

        // 关键词搜索
        if ($keyword) {
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('username', 'like', "%{$keyword}%")
                  ->orWhere('nick_name', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        // 状态筛选
        if ($status !== null) {
            $query->where('status', $status);
        }

        // 角色筛选
        if ($roleId) {
            $query->whereHas('roles', function (Builder $q) use ($roleId) {
                $q->where('roles.id', $roleId);
            });
        }

        return $query->orderBy('created_at', 'desc')
                    ->paginate($perPage);
    }

    /**
     * 创建管理员
     *
     * @param array $data
     * @return Admin
     */
    public function createAdmin(array $data): Admin
    {
        return DB::transaction(function () use ($data) {
            // 处理密码
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            // 提取角色ID
            $roleIds = $data['role_ids'] ?? [];
            unset($data['role_ids']);

            // 创建管理员
            $admin = Admin::create($data);

            // 分配角色
            if (!empty($roleIds)) {
                $admin->roles()->sync($roleIds);
            }

            return $admin->load('roles');
        });
    }

    /**
     * 更新管理员
     *
     * @param Admin $admin
     * @param array $data
     * @return Admin
     */
    public function updateAdmin(Admin $admin, array $data): Admin
    {
        return DB::transaction(function () use ($admin, $data) {
            // 处理密码
            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            // 提取角色ID
            $roleIds = $data['role_ids'] ?? null;
            unset($data['role_ids']);

            // 更新管理员信息
            $admin->update($data);

            // 更新角色关联
            if ($roleIds !== null) {
                $admin->roles()->sync($roleIds);
            }

            return $admin->load('roles');
        });
    }

    /**
     * 删除管理员
     *
     * @param Admin $admin
     * @return bool
     * @throws AdminException
     */
    public function deleteAdmin(Admin $admin): bool
    {
        // 不能删除自己
        if ($admin->id === auth('admin')->id()) {
            throw AdminException::cannotDeleteSelf();
        }

        return DB::transaction(function () use ($admin) {
            // 删除角色关联
            $admin->roles()->detach();
            
            // 删除管理员
            return $admin->delete();
        });
    }

    /**
     * 分配角色
     *
     * @param Admin $admin
     * @param array $roleIds
     * @param int $operatorId
     * @param string $ip
     * @param string $userAgent
     * @return void
     */
    public function assignRoles(Admin $admin, array $roleIds, int $operatorId, string $ip, string $userAgent): void
    {
        // 验证角色ID是否存在
        if (!empty($roleIds)) {
            $existingRoleIds = \App\Models\Role::whereIn('id', $roleIds)->pluck('id')->toArray();
            $invalidRoleIds = array_diff($roleIds, $existingRoleIds);
            
            if (!empty($invalidRoleIds)) {
                throw new \InvalidArgumentException('The selected role ids are invalid: ' . implode(', ', $invalidRoleIds));
            }
        }
        
        DB::transaction(function () use ($admin, $roleIds, $operatorId, $ip, $userAgent) {
            // 同步角色关联
            $admin->roles()->sync($roleIds);

            // 记录操作日志
            $this->recordAdminOperation(
                '分配管理员角色',
                [
                    'admin_id' => $admin->id,
                    'role_ids' => $roleIds,
                    'operator_id' => $operatorId
                ],
                'POST',
                'api/admins/' . $admin->id . '/roles',
                $ip,
                $userAgent
            );
        });
    }

    /**
     * 更新管理员状态
     *
     * @param Admin $admin
     * @param int $status
     * @return bool
     * @throws AdminException
     */
    public function updateStatus(Admin $admin, int $status): bool
    {
        // 不能禁用自己
        if ($admin->id === auth('admin')->id() && $status == 0) {
            throw AdminException::cannotDisableSelf();
        }

        return $admin->update(['status' => $status]);
    }

    /**
     * 获取管理员的权限
     *
     * @param Admin $admin
     * @return array
     */
    public function getAdminPermissions(Admin $admin): array
    {
        $permissions = [];
        
        foreach ($admin->roles as $role) {
            // 获取角色的资源权限
            foreach ($role->resources as $resource) {
                $permissions[] = $resource->route_name;
            }
        }
        
        return array_unique($permissions);
    }

    /**
     * 检查管理员是否有指定权限
     *
     * @param Admin $admin
     * @param string $permission
     * @return bool
     */
    public function hasPermission(Admin $admin, string $permission): bool
    {
        $permissions = $this->getAdminPermissions($admin);
        
        // 支持通配符权限检查
        foreach ($permissions as $userPermission) {
            if ($this->matchPermission($userPermission, $permission)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * 权限匹配（支持通配符）
     *
     * @param string $userPermission
     * @param string $requiredPermission
     * @return bool
     */
    private function matchPermission(string $userPermission, string $requiredPermission): bool
    {
        // 完全匹配
        if ($userPermission === $requiredPermission) {
            return true;
        }
        
        // 通配符匹配
        if (str_ends_with($userPermission, '*')) {
            $prefix = rtrim($userPermission, '*');
            return str_starts_with($requiredPermission, $prefix);
        }
        
        return false;
    }

    /**
     * 获取管理员的菜单
     *
     * @param Admin $admin
     * @return array
     */
    public function getAdminMenus(Admin $admin): array
    {
        $menuIds = [];
        
        foreach ($admin->roles as $role) {
            foreach ($role->menus as $menu) {
                $menuIds[] = $menu->id;
            }
        }
        
        $menuIds = array_unique($menuIds);
        
        // 获取菜单并构建树形结构
        $menus = \App\Models\Menu::whereIn('id', $menuIds)
            ->where('hidden', 0)
            ->orderBy('sort')
            ->get();
            
        return $this->buildMenuTree($menus->toArray());
    }

    /**
     * 构建菜单树
     *
     * @param array $menus
     * @param int $parentId
     * @return array
     */
    private function buildMenuTree(array $menus, int $parentId = 0): array
    {
        $tree = [];
        
        foreach ($menus as $menu) {
            if ($menu['parent_id'] == $parentId) {
                $children = $this->buildMenuTree($menus, $menu['id']);
                if (!empty($children)) {
                    $menu['children'] = $children;
                }
                $tree[] = $menu;
            }
        }
        
        return $tree;
    }
}