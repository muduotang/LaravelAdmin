<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Admin;
use App\Exceptions\BusinessException;
use App\Traits\AdminOperationLoggable;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class RoleService
{
    use AdminOperationLoggable;

    /**
     * 获取角色列表
     *
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function list(array $params): LengthAwarePaginator
    {
        $query = Role::query();

        // 如果有搜索关键词
        if (!empty($params['keyword'])) {
            $query->where(function ($q) use ($params) {
                $q->where('name', 'like', "%{$params['keyword']}%")
                    ->orWhere('description', 'like', "%{$params['keyword']}%");
            });
        }

        return $query->paginate($params['per_page'] ?? 15);
    }

    /**
     * 创建角色
     *
     * @param array $data
     * @param int $adminId
     * @param string $ip
     * @param string $userAgent
     * @return Role
     */
    public function create(array $data, int $adminId, string $ip, string $userAgent): Role
    {
        return DB::transaction(function () use ($data, $adminId, $ip, $userAgent) {
            $role = Role::create([
                'name' => $data['name'],
                'description' => $data['description'],
            ]);

            // 记录操作日志
            $this->recordAdminOperation(
                '创建角色',
                $data,
                'POST',
                'api/roles',
                $ip,
                $userAgent
            );

            return $role;
        });
    }

    /**
     * 更新角色
     *
     * @param Role $role
     * @param array $data
     * @param int $adminId
     * @param string $ip
     * @param string $userAgent
     * @return Role
     */
    public function update(Role $role, array $data, int $adminId, string $ip, string $userAgent): Role
    {
        return DB::transaction(function () use ($role, $data, $adminId, $ip, $userAgent) {
            $role->update([
                'name' => $data['name'],
                'description' => $data['description'],
            ]);

            // 记录操作日志
            $this->recordAdminOperation(
                '更新角色',
                $data,
                'PUT',
                'api/roles/' . $role->id,
                $ip,
                $userAgent
            );

            return $role->fresh();
        });
    }

    /**
     * 删除角色
     *
     * @param Role $role
     * @param int $adminId
     * @param string $ip
     * @param string $userAgent
     * @return bool
     * @throws BusinessException
     */
    public function delete(Role $role, int $adminId, string $ip, string $userAgent): bool
    {
        // 检查是否有管理员使用此角色
        if ($role->admins()->exists()) {
            throw new BusinessException('该角色下还有管理员，无法删除');
        }

        return DB::transaction(function () use ($role, $adminId, $ip, $userAgent) {
            $result = $role->delete();

            // 记录操作日志
            $this->recordAdminOperation(
                '删除角色',
                ['id' => $role->id],
                'DELETE',
                'api/roles/' . $role->id,
                $ip,
                $userAgent
            );

            return $result;
        });
    }

    /**
     * 分配权限给角色
     *
     * @param Role $role
     * @param array $permissionIds
     * @param string $ip
     * @param string $userAgent
     * @return void
     */
    public function assignPermissions(Role $role, array $permissionIds, string $ip, string $userAgent): void
    {
        DB::transaction(function () use ($role, $permissionIds, $ip, $userAgent) {
            // 先删除原有的权限
            $role->permissions()->detach();
            // 添加新的权限
            $role->permissions()->attach($permissionIds);

            // 记录操作日志
            $this->recordAdminOperation(
                '分配角色权限',
                [
                    'role_id' => $role->id,
                    'permission_ids' => $permissionIds
                ],
                'POST',
                'api/roles/' . $role->id . '/permissions',
                $ip,
                $userAgent
            );
        });
    }

    /**
     * 获取角色的所有权限ID
     *
     * @param Role $role
     * @return Collection
     */
    public function getPermissions(Role $role): Collection
    {
        return $role->permissions()->pluck('id');
    }

    /**
     * 为角色分配菜单
     *
     * @param Role $role
     * @param array $menuIds
     * @param int $adminId
     * @param string $ip
     * @param string $userAgent
     * @return void
     * @throws BusinessException
     */
    public function assignMenus(Role $role, array $menuIds, int $adminId, string $ip, string $userAgent): void
    {
        // 验证菜单ID是否存在
        if (!empty($menuIds)) {
            $existingMenuIds = \App\Models\Menu::whereIn('id', $menuIds)->pluck('id')->toArray();
            $invalidMenuIds = array_diff($menuIds, $existingMenuIds);
            
            if (!empty($invalidMenuIds)) {
                throw new BusinessException('选择的菜单不存在');
            }
        }
        
        DB::transaction(function () use ($role, $menuIds, $adminId, $ip, $userAgent) {
            // 先删除原有的菜单
            $role->menus()->detach();
            // 添加新的菜单
            if (!empty($menuIds)) {
                $role->menus()->attach($menuIds);
            }

            // 记录操作日志
            $this->recordAdminOperation(
                '分配角色菜单',
                [
                    'role_id' => $role->id,
                    'menu_ids' => $menuIds
                ],
                'POST',
                'api/roles/' . $role->id . '/menus',
                $ip,
                $userAgent
            );
        });
    }

    /**
     * 为角色分配资源
     *
     * @param Role $role
     * @param array $resourceIds
     * @param int $adminId
     * @param string $ip
     * @param string $userAgent
     * @return void
     * @throws BusinessException
     */
    public function assignResources(Role $role, array $resourceIds, int $adminId, string $ip, string $userAgent): void
    {
        // 验证资源ID是否存在
        if (!empty($resourceIds)) {
            $existingResourceIds = \App\Models\Resource::whereIn('id', $resourceIds)->pluck('id')->toArray();
            $invalidResourceIds = array_diff($resourceIds, $existingResourceIds);
            
            if (!empty($invalidResourceIds)) {
                throw new BusinessException('选择的资源不存在');
            }
        }
        
        DB::transaction(function () use ($role, $resourceIds, $adminId, $ip, $userAgent) {
            // 先删除原有的资源
            $role->resources()->detach();
            // 添加新的资源
            if (!empty($resourceIds)) {
                $role->resources()->attach($resourceIds);
            }

            // 记录操作日志
            $this->recordAdminOperation(
                '分配角色资源',
                [
                    'role_id' => $role->id,
                    'resource_ids' => $resourceIds
                ],
                'POST',
                'api/roles/' . $role->id . '/resources',
                $ip,
                $userAgent
            );
        });
    }

    /**
     * 获取角色的所有菜单ID
     *
     * @param Role $role
     * @return \Illuminate\Support\Collection
     */
    public function getMenus(Role $role): \Illuminate\Support\Collection
    {
        return $role->menus()->pluck('menus.id');
    }

    /**
     * 获取角色的所有资源ID
     *
     * @param Role $role
     * @return \Illuminate\Support\Collection
     */
    public function getResources(Role $role): \Illuminate\Support\Collection
    {
        return $role->resources()->pluck('resources.id');
    }
}