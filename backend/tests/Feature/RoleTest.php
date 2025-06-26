<?php

namespace Tests\Feature;

use App\Models\Admin;
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
} 