<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\Role;
use App\Models\Menu;
use App\Models\Resource;
use App\Models\ResourceCategory;
use Illuminate\Support\Facades\Hash;

class AdminTableSeeder extends Seeder
{
    public function run(): void
    {
        // 1. 创建超级管理员角色
        $superAdminRole = Role::create([
            'name' => 'super-admin',
            'description' => '超级管理员',
            'status' => 1,
            'sort' => 0,
        ]);

        // 2. 创建测试管理员角色
        $testAdminRole = Role::create([
            'name' => 'test-admin',
            'description' => '测试管理员',
            'status' => 1,
            'sort' => 1,
        ]);

        // 3. 创建超级管理员用户
        $superAdmin = Admin::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'email' => 'admin@example.com',
            'nick_name' => '超级管理员',
            'status' => 1,
        ]);

        // 4. 创建测试管理员用户
        $testAdmin = Admin::create([
            'username' => 'test',
            'password' => Hash::make('test123'),
            'email' => 'test@example.com',
            'nick_name' => '测试管理员',
            'status' => 1,
        ]);

        // 5. 分配角色
        $superAdmin->roles()->attach($superAdminRole);
        $testAdmin->roles()->attach($testAdminRole);

        // 6. 创建资源分类
        $systemCategory = ResourceCategory::create([
            'name' => '系统管理',
            'sort' => 0,
        ]);

        $userCategory = ResourceCategory::create([
            'name' => '用户管理',
            'sort' => 1,
        ]);

        // 7. 创建资源
        $resources = [
            // 系统管理资源
            [
                'category_id' => $systemCategory->id,
                'name' => '所有权限',
                'route_name' => '*',
                'description' => '超级管理员权限',
            ],
            [
                'category_id' => $systemCategory->id,
                'name' => '角色管理',
                'route_name' => 'roles.*',
                'description' => '角色管理相关权限',
            ],
            // 用户管理资源
            [
                'category_id' => $userCategory->id,
                'name' => '用户查看',
                'route_name' => 'users.index',
                'description' => '查看用户列表权限',
            ],
            [
                'category_id' => $userCategory->id,
                'name' => '用户详情',
                'route_name' => 'users.show',
                'description' => '查看用户详情权限',
            ],
        ];

        foreach ($resources as $resource) {
            Resource::create($resource);
        }

        // 8. 创建菜单
        $menus = [
            // 系统管理
            [
                'title' => '系统管理',
                'level' => 0,
                'sort' => 0,
                'name' => 'system',
                'icon' => 'setting',
                'children' => [
                    [
                        'title' => '用户管理',
                        'level' => 1,
                        'sort' => 0,
                        'name' => 'users',
                        'icon' => 'user',
                    ],
                    [
                        'title' => '角色管理',
                        'level' => 1,
                        'sort' => 1,
                        'name' => 'roles',
                        'icon' => 'team',
                    ],
                    [
                        'title' => '菜单管理',
                        'level' => 1,
                        'sort' => 2,
                        'name' => 'menus',
                        'icon' => 'menu',
                    ],
                ],
            ],
            // 权限管理
            [
                'title' => '权限管理',
                'level' => 0,
                'sort' => 1,
                'name' => 'permissions',
                'icon' => 'safety',
                'children' => [
                    [
                        'title' => '资源管理',
                        'level' => 1,
                        'sort' => 0,
                        'name' => 'resources',
                        'icon' => 'api',
                    ],
                    [
                        'title' => '资源分类',
                        'level' => 1,
                        'sort' => 1,
                        'name' => 'resource-categories',
                        'icon' => 'folder',
                    ],
                ],
            ],
        ];

        foreach ($menus as $menuData) {
            $children = $menuData['children'] ?? [];
            unset($menuData['children']);
            
            $menu = Menu::create($menuData);
            
            if ($children) {
                foreach ($children as $child) {
                    $child['parent_id'] = $menu->id;
                    Menu::create($child);
                }
            }
        }

        // 9. 为超级管理员分配所有资源和菜单
        $allResources = Resource::all();
        $allMenus = Menu::all();
        
        $superAdminRole->resources()->attach($allResources->pluck('id'));
        $superAdminRole->menus()->attach($allMenus->pluck('id'));

        // 10. 为测试管理员分配部分资源和菜单
        $testResources = Resource::whereIn('route_name', ['users.index', 'users.show'])->get();
        $testMenus = Menu::where('name', 'users')->get();
        
        $testAdminRole->resources()->attach($testResources->pluck('id'));
        $testAdminRole->menus()->attach($testMenus->pluck('id'));

        // 更新角色的用户数量
        Role::all()->each(function ($role) {
            $role->update(['admin_count' => $role->admins()->count()]);
        });
    }
} 