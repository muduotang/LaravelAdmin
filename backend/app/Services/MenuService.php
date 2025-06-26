<?php

namespace App\Services;

use App\Models\Menu;
use App\Traits\AdminOperationLoggable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class MenuService
{
    use AdminOperationLoggable;

    /**
     * 获取菜单树形结构
     *
     * @return array
     */
    public function getMenuTree(): array
    {
        $menus = Menu::orderBy('sort')->get();
        return $this->buildTree($menus);
    }

    /**
     * 获取菜单列表
     *
     * @return Collection
     */
    public function getMenuList(): Collection
    {
        return Menu::orderBy('sort')->get();
    }

    /**
     * 创建菜单
     *
     * @param array $data
     * @return Menu
     */
    public function createMenu(array $data): Menu
    {
        DB::beginTransaction();
        try {
            $menu = Menu::create($data);
            
            $this->recordAdminOperation(
                'create_menu',
                ['menu_id' => $menu->id, 'title' => $menu->title]
            );
            
            DB::commit();
            return $menu;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 更新菜单
     *
     * @param Menu $menu
     * @param array $data
     * @return Menu
     */
    public function updateMenu(Menu $menu, array $data): Menu
    {
        DB::beginTransaction();
        try {
            $menu->update($data);
            
            $this->recordAdminOperation(
                'update_menu',
                ['menu_id' => $menu->id, 'title' => $menu->title]
            );
            
            DB::commit();
            return $menu;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 删除菜单
     *
     * @param Menu $menu
     * @return bool
     */
    public function deleteMenu(Menu $menu): bool
    {
        DB::beginTransaction();
        try {
            // 检查是否有子菜单
            if ($menu->children()->exists()) {
                throw new \Exception('该菜单下有子菜单，不能删除');
            }
            
            $menuId = $menu->id;
            $menuTitle = $menu->title;
            
            $menu->delete();
            
            $this->recordAdminOperation(
                'delete_menu',
                ['menu_id' => $menuId, 'title' => $menuTitle]
            );
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 构建树形结构
     *
     * @param Collection $menus
     * @param int|null $parentId
     * @return array
     */
    protected function buildTree(Collection $menus, ?int $parentId = null): array
    {
        $branch = [];
        
        foreach ($menus as $menu) {
            if ($menu->parent_id === $parentId) {
                $children = $this->buildTree($menus, $menu->id);
                
                if ($children) {
                    $menu->children = $children;
                }
                
                $branch[] = $menu;
            }
        }
        
        return $branch;
    }
} 