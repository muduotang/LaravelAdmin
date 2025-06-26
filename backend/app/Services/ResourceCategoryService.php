<?php

namespace App\Services;

use App\Models\ResourceCategory;
use App\Traits\AdminOperationLoggable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ResourceCategoryService
{
    use AdminOperationLoggable;

    /**
     * 获取资源分类列表
     *
     * @return Collection
     */
    public function getList(): Collection
    {
        return ResourceCategory::orderBy('sort')->get();
    }

    /**
     * 创建资源分类
     *
     * @param array $data
     * @return ResourceCategory
     */
    public function create(array $data): ResourceCategory
    {
        DB::beginTransaction();
        try {
            $category = ResourceCategory::create($data);
            
            $this->recordAdminOperation(
                'create_resource_category',
                ['category_id' => $category->id, 'name' => $category->name]
            );
            
            DB::commit();
            return $category;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 更新资源分类
     *
     * @param ResourceCategory $category
     * @param array $data
     * @return ResourceCategory
     */
    public function update(ResourceCategory $category, array $data): ResourceCategory
    {
        DB::beginTransaction();
        try {
            $category->update($data);
            
            $this->recordAdminOperation(
                'update_resource_category',
                ['category_id' => $category->id, 'name' => $category->name]
            );
            
            DB::commit();
            return $category;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 删除资源分类
     *
     * @param ResourceCategory $category
     * @return bool
     */
    public function delete(ResourceCategory $category): bool
    {
        DB::beginTransaction();
        try {
            // 检查是否有关联的资源
            if ($category->resources()->exists()) {
                throw new \Exception('该分类下有资源，不能删除');
            }
            
            $categoryId = $category->id;
            $categoryName = $category->name;
            
            $category->delete();
            
            $this->recordAdminOperation(
                'delete_resource_category',
                ['category_id' => $categoryId, 'name' => $categoryName]
            );
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
} 