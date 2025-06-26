<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Menu;
use App\Models\Resource;
use App\Models\ResourceCategory;
use App\Models\Role;
use Tests\TestCase;

class RoleTest extends TestCase
{
    /** @test */
    public function 未登录用户不能访问角色管理接口()
    {
        // 尝试获取角色列表
        $this->getJson('/api/roles')->assertUnauthorized();

        // 尝试创建角色
        $this->postJson('/api/roles', [
            'name' => '新角色',
            'description' => '新角色描述',
        ])->assertUnauthorized();

        // 尝试更新角色
        $this->putJson('/api/roles/1', [
            'name' => '更新的角色',
            'description' => '更新的角色描述',
        ])->assertUnauthorized();

        // 尝试删除角色
        $this->deleteJson('/api/roles/1')->assertUnauthorized();
    }

    /** @test */
    public function 已登录管理员可以获取角色列表()
    {
        // 创建管理员和角色
        $admin = Admin::factory()->create();
        Role::factory()->count(3)->create();

        // 以管理员身份请求
        $response = $this->actingAs($admin, 'admin')
            ->getJson('/api/roles');

        $response->assertOk()
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    /** @test */
    public function 已登录管理员可以创建角色()
    {
        // 创建管理员
        $admin = Admin::factory()->create();
        
        $data = [
            'name' => '新角色',
            'description' => '新角色描述',
        ];

        $response = $this->actingAs($admin, 'admin')
            ->postJson('/api/roles', $data);

        $response->assertOk()
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('roles', $data);
    }

    /** @test */
    public function 已登录管理员可以更新角色()
    {
        // 创建管理员和角色
        $admin = Admin::factory()->create();
        $role = Role::factory()->create();
        
        $data = [
            'name' => '更新的角色',
            'description' => '更新的角色描述',
        ];

        $response = $this->actingAs($admin, 'admin')
            ->putJson("/api/roles/{$role->id}", $data);

        $response->assertOk()
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => $data['name'],
            'description' => $data['description'],
        ]);
    }

    /** @test */
    public function 已登录管理员可以删除角色()
    {
        // 创建管理员和角色
        $admin = Admin::factory()->create();
        $role = Role::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->deleteJson("/api/roles/{$role->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('roles', [
            'id' => $role->id,
        ]);
    }

    /** @test */
    public function 角色名称不能重复()
    {
        // 创建管理员和角色
        $admin = Admin::factory()->create();
        $role = Role::factory()->create();
        
        $data = [
            'name' => $role->name,
            'description' => '新角色描述',
        ];

        $response = $this->actingAs($admin, 'admin')
            ->postJson('/api/roles', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function 角色名称和描述是必填项()
    {
        // 创建管理员
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->postJson('/api/roles', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'description']);
    }

    /** @test */
    public function 已登录管理员可以为角色分配菜单()
    {
        // 创建管理员、角色和菜单
        $admin = Admin::factory()->create();
        $role = Role::factory()->create();
        $menus = Menu::factory()->count(3)->create();
        
        $data = [
            'menu_ids' => $menus->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($admin, 'admin')
            ->postJson("/api/roles/{$role->id}/menus", $data);

        $response->assertOk()
            ->assertJson([
                'code' => 200,
                'message' => '菜单分配成功',
            ]);

        // 验证数据库中的关联关系
        foreach ($menus as $menu) {
            $this->assertDatabaseHas('role_menu', [
                'role_id' => $role->id,
                'menu_id' => $menu->id,
            ]);
        }
    }

    /** @test */
    public function 已登录管理员可以为角色分配资源()
    {
        // 创建管理员、角色、资源分类和资源
        $admin = Admin::factory()->create();
        $role = Role::factory()->create();
        $category = ResourceCategory::factory()->create();
        $resources = Resource::factory()->count(3)->create([
            'category_id' => $category->id,
        ]);
        
        $data = [
            'resource_ids' => $resources->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($admin, 'admin')
            ->postJson("/api/roles/{$role->id}/resources", $data);

        $response->assertOk()
            ->assertJson([
                'code' => 200,
                'message' => '资源分配成功',
            ]);

        // 验证数据库中的关联关系
        foreach ($resources as $resource) {
            $this->assertDatabaseHas('role_resource', [
                'role_id' => $role->id,
                'resource_id' => $resource->id,
            ]);
        }
    }

    /** @test */
    public function 分配菜单时菜单ID必须存在()
    {
        $admin = Admin::factory()->create();
        $role = Role::factory()->create();
        
        $data = [
            'menu_ids' => [999, 1000], // 不存在的菜单ID
        ];

        $response = $this->actingAs($admin, 'admin')
            ->postJson("/api/roles/{$role->id}/menus", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['menu_ids']);
    }

    /** @test */
    public function 分配资源时资源ID必须存在()
    {
        $admin = Admin::factory()->create();
        $role = Role::factory()->create();
        
        $data = [
            'resource_ids' => [999, 1000], // 不存在的资源ID
        ];

        $response = $this->actingAs($admin, 'admin')
            ->postJson("/api/roles/{$role->id}/resources", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['resource_ids']);
    }

    /** @test */
    public function 未登录用户不能分配角色权限()
    {
        $role = Role::factory()->create();
        
        // 尝试分配菜单
        $this->postJson("/api/roles/{$role->id}/menus", [
            'menu_ids' => [1, 2],
        ])->assertUnauthorized();

        // 尝试分配资源
        $this->postJson("/api/roles/{$role->id}/resources", [
            'resource_ids' => [1, 2],
        ])->assertUnauthorized();
    }
}