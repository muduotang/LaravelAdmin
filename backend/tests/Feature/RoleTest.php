<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Menu;
use App\Models\Resource;
use App\Models\ResourceCategory;
use App\Models\Role;
use Tests\TestCase;
use App\Services\RoleService;
use App\Exceptions\BusinessException;

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
        $admin = Admin::factory()->create();
        $role = Role::factory()->create();
        $menus = Menu::factory()->count(2)->create();
        $data = [
            'menu_ids' => $menus->pluck('id')->toArray()
        ];

        $response = $this->actingAs($admin, 'admin')
            ->postJson("/api/roles/{$role->id}/menus", $data);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'code' => 200,
                'message' => '菜单分配成功'
            ]);
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
            'menu_ids' => [999, 1000]
        ];

        $response = $this->actingAs($admin, 'admin')
            ->postJson("/api/roles/{$role->id}/menus", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['menu_ids.0', 'menu_ids.1']);
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

    /** @test */
    public function 已登录管理员可以获取单个角色详情()
    {
        $admin = Admin::factory()->create();
        $role = Role::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->getJson("/api/roles/{$role->id}");

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
    }

    /** @test */
    public function 已登录管理员可以获取角色的菜单列表()
    {
        $admin = Admin::factory()->create();
        $role = Role::factory()->create();
        $menus = Menu::factory()->count(3)->create();
        
        // 为角色分配菜单
        $role->menus()->attach($menus->pluck('id'));

        $response = $this->actingAs($admin, 'admin')
            ->getJson("/api/roles/{$role->id}/menus");

        $response->assertOk()
            ->assertJsonStructure([
                'code',
                'message',
                'data',
            ]);
    }

    /** @test */
    public function 已登录管理员可以获取角色的资源列表()
    {
        $admin = Admin::factory()->create();
        $role = Role::factory()->create();
        $category = ResourceCategory::factory()->create();
        $resources = Resource::factory()->count(3)->create([
            'category_id' => $category->id,
        ]);
        
        // 为角色分配资源
        $role->resources()->attach($resources->pluck('id'));

        $response = $this->actingAs($admin, 'admin')
            ->getJson("/api/roles/{$role->id}/resources");

        $response->assertOk()
            ->assertJsonStructure([
                'code',
                'message',
                'data',
            ]);
    }

    /** @test */
    public function 不能删除有管理员关联的角色()
    {
        $admin = Admin::factory()->create();
        $role = Role::factory()->create();
        
        // 为角色分配管理员
        $testAdmin = Admin::factory()->create();
        $testAdmin->roles()->attach($role);

        $response = $this->actingAs($admin, 'admin')
            ->deleteJson("/api/roles/{$role->id}");

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => '该角色下还有管理员，无法删除',
            ]);

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
        ]);
    }

    /** @test */
    public function 角色名称长度验证()
    {
        $admin = Admin::factory()->create();
        
        $data = [
            'name' => str_repeat('a', 51), // 超过50字符
            'description' => '角色描述',
        ];

        $response = $this->actingAs($admin, 'admin')
            ->postJson('/api/roles', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function 角色描述长度验证()
    {
        $admin = Admin::factory()->create();
        
        $data = [
            'name' => '测试角色',
            'description' => str_repeat('a', 256), // 超过255字符
        ];

        $response = $this->actingAs($admin, 'admin')
            ->postJson('/api/roles', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['description']);
    }

    /** @test */
    public function 更新角色时名称不能与其他角色重复()
    {
        $admin = Admin::factory()->create();
        $role1 = Role::factory()->create(['name' => '角色1']);
        $role2 = Role::factory()->create(['name' => '角色2']);
        
        $data = [
            'name' => $role1->name, // 使用已存在的角色名称
            'description' => '更新的描述',
        ];

        $response = $this->actingAs($admin, 'admin')
            ->putJson("/api/roles/{$role2->id}", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function 更新角色时可以使用相同的名称()
    {
        $admin = Admin::factory()->create();
        $role = Role::factory()->create(['name' => '测试角色']);
        
        $data = [
            'name' => $role->name, // 使用相同的名称
            'description' => '更新的描述',
        ];

        $response = $this->actingAs($admin, 'admin')
            ->putJson("/api/roles/{$role->id}", $data);

        $response->assertOk();
        
        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => $data['name'],
            'description' => $data['description'],
        ]);
    }

    /** @test */
    public function 分配菜单时菜单ID数组为空应该成功()
    {
        $admin = Admin::factory()->create();
        $role = Role::factory()->create();
        $data = [
            'menu_ids' => []
        ];

        $response = $this->actingAs($admin, 'admin')
            ->postJson("/api/roles/{$role->id}/menus", $data);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'code' => 200,
                'message' => '菜单分配成功'
            ]);
    }

    /** @test */
    public function 分配资源时资源ID数组为空应该成功()
    {
        $admin = Admin::factory()->create();
        $role = Role::factory()->create();
        
        $data = [
            'resource_ids' => [], // 空数组
        ];

        $response = $this->actingAs($admin, 'admin')
            ->postJson("/api/roles/{$role->id}/resources", $data);

        $response->assertOk()
            ->assertJson([
                'code' => 200,
                'message' => '资源分配成功',
            ]);
    }

    /** @test */
    public function 分配菜单时缺少menu_ids参数应该失败()
    {
        $admin = Admin::factory()->create();
        $role = Role::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->postJson("/api/roles/{$role->id}/menus", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['menu_ids']);
    }

    /** @test */
    public function 分配资源时缺少resource_ids参数应该失败()
    {
        $admin = Admin::factory()->create();
        $role = Role::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->postJson("/api/roles/{$role->id}/resources", []);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'code' => 422,
                'message' => 'Validation failed.',
            ])
            ->assertJsonPath('errors.resource_ids.0', 'The resource ids field must be present.');
    }

    /** @test */
    public function 未登录用户不能获取角色详情()
    {
        $role = Role::factory()->create();

        $this->getJson("/api/roles/{$role->id}")->assertUnauthorized();
    }

    /** @test */
    public function 未登录用户不能获取角色菜单()
    {
        $role = Role::factory()->create();

        $this->getJson("/api/roles/{$role->id}/menus")->assertUnauthorized();
    }

    /** @test */
    public function 未登录用户不能获取角色资源()
    {
        $role = Role::factory()->create();

        $this->getJson("/api/roles/{$role->id}/resources")->assertUnauthorized();
    }

    /** @test */
    public function 访问不存在的角色应该返回404()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->getJson('/api/roles/999');

        $response->assertNotFound();
    }

    /** @test */
    public function 更新不存在的角色应该返回404()
    {
        $admin = Admin::factory()->create();
        
        $data = [
            'name' => '更新的角色',
            'description' => '更新的描述',
        ];

        $response = $this->actingAs($admin, 'admin')
            ->putJson('/api/roles/999', $data);

        $response->assertNotFound();
    }

    /** @test */
    public function 删除不存在的角色应该返回404()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->deleteJson('/api/roles/999');

        $response->assertNotFound();
    }

    /** @test */
    public function 分配菜单时服务层抛出业务异常应该返回400错误()
    {
        $admin = Admin::factory()->create();
        $role = Role::factory()->create();
        $menus = Menu::factory()->count(2)->create();

        $this->mock(RoleService::class, function ($mock) {
            $mock->shouldReceive('assignMenus')
                ->once()
                ->andThrow(new BusinessException('选择的菜单不存在'));
        });

        $response = $this->actingAs($admin, 'admin')
            ->postJson("/api/roles/{$role->id}/menus", [
                'menu_ids' => $menus->pluck('id')->toArray()
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'code' => 400,
                'message' => '选择的菜单不存在',
                'data' => null
            ]);
    }
}