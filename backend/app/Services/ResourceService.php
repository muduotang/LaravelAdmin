<?php

namespace App\Services;

use App\Models\Resource;
use App\Traits\AdminOperationLoggable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ResourceService
{
    use AdminOperationLoggable;

    /**
     * 获取资源列表
     *
     * @return Collection
     */
    public function getList(): Collection
    {
        return Resource::with('category')
            ->orderBy('id')
            ->get();
    }

    /**
     * 创建资源
     *
     * @param array $data
     * @return Resource
     */
    public function create(array $data): Resource
    {
        DB::beginTransaction();
        try {
            $resource = Resource::create($data);
            
            $this->recordAdminOperation(
                'create_resource',
                [
                    'resource_id' => $resource->id,
                    'name' => $resource->name,
                    'route_name' => $resource->route_name,
                ]
            );
            
            DB::commit();
            return $resource;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 更新资源
     *
     * @param Resource $resource
     * @param array $data
     * @return Resource
     */
    public function update(Resource $resource, array $data): Resource
    {
        DB::beginTransaction();
        try {
            $resource->update($data);
            
            $this->recordAdminOperation(
                'update_resource',
                [
                    'resource_id' => $resource->id,
                    'name' => $resource->name,
                    'route_name' => $resource->route_name,
                ]
            );
            
            DB::commit();
            return $resource;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 删除资源
     *
     * @param Resource $resource
     * @return bool
     */
    public function delete(Resource $resource): bool
    {
        DB::beginTransaction();
        try {
            // 检查是否有角色关联
            if ($resource->roles()->exists()) {
                throw new \Exception('该资源已被角色使用，不能删除');
            }
            
            $resourceId = $resource->id;
            $resourceName = $resource->name;
            $routeName = $resource->route_name;
            
            $resource->delete();
            
            $this->recordAdminOperation(
                'delete_resource',
                [
                    'resource_id' => $resourceId,
                    'name' => $resourceName,
                    'route_name' => $routeName,
                ]
            );
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
} 