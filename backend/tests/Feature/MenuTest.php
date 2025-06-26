<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Menu;
use Tests\TestCase;

class MenuTest extends TestCase
{
    /** @test */
    public function 未登录用户不能访问菜单管理接口()
    {
        // 尝试获取菜单列表
        $this->getJson('/api/menus')->assertUnauthorized();

        // 尝试获取菜单树
        $this->getJson('/api/menus/tree')->assertUnauthorized();

        // 尝试创建菜单
        $this->postJson('/api/menus', [
            'title' => '新菜单',
            'level' => 0,
            'sort' => 0,
            'hidden' => false,
            'keep_alive' => true,
        ])->assertUnauthorized();

        // 尝试更新菜单
        $this->putJson('/api/menus/1', [
            'title' => '更新的菜单',
            'level' => 0,
            'sort' => 0,
            'hidden' => false,
            'keep_alive' => true,
        ])->assertUnauthorized();

        // 尝试删除菜单
        $this->deleteJson('/api/menus/1')->assertUnauthorized();
    }

    /** @test */
    public function 已登录管理员可以获取菜单列表()
    {
        // 创建管理员和菜单
        $admin = Admin::factory()->create();
        Menu::factory()->count(3)->create();

        // 以管理员身份请求
        $response = $this->actingAs($admin, 'admin')
            ->getJson('/api/menus');

        $response->assertOk()
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'parent_id',
                        'title',
                        'level',
                        'sort',
                        'name',
                        'icon',
                        'hidden',
                        'keep_alive',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    /** @test */
    public function 已登录管理员可以获取菜单树()
    {
        // 创建管理员和菜单
        $admin = Admin::factory()->create();
        $parentMenu = Menu::factory()->create();
        Menu::factory()->count(2)->child($parentMenu->id, 1)->create();

        // 以管理员身份请求
        $response = $this->actingAs($admin, 'admin')
            ->getJson('/api/menus/tree');

        $response->assertOk()
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'parent_id',
                        'title',
                        'level',
                        'sort',
                        'name',
                        'icon',
                        'hidden',
                        'keep_alive',
                        'created_at',
                        'updated_at',
                        'children',
                    ],
                ],
            ]);
    }

    /** @test */
    public function 已登录管理员可以创建菜单()
    {
        // 创建管理员
        $admin = Admin::factory()->create();
        
        $data = [
            'parent_id' => null,
            'title' => '新菜单',
            'level' => 0,
            'sort' => 0,
            'name' => 'new-menu',
            'icon' => 'el-icon-menu',
            'hidden' => false,
            'keep_alive' => true,
        ];

        $response = $this->actingAs($admin, 'admin')
            ->postJson('/api/menus', $data);

        $response->assertOk()
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    'id',
                    'parent_id',
                    'title',
                    'level',
                    'sort',
                    'name',
                    'icon',
                    'hidden',
                    'keep_alive',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('menus', $data);
    }

    /** @test */
    public function 已登录管理员可以更新菜单()
    {
        // 创建管理员和菜单
        $admin = Admin::factory()->create();
        $menu = Menu::factory()->create();
        
        $data = [
            'parent_id' => null,
            'title' => '更新的菜单',
            'level' => 0,
            'sort' => 1,
            'name' => 'updated-menu',
            'icon' => 'el-icon-setting',
            'hidden' => true,
            'keep_alive' => false,
        ];

        $response = $this->actingAs($admin, 'admin')
            ->putJson('/api/menus/' . $menu->id, $data);

        $response->assertOk()
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    'id',
                    'parent_id',
                    'title',
                    'level',
                    'sort',
                    'name',
                    'icon',
                    'hidden',
                    'keep_alive',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('menus', array_merge(['id' => $menu->id], $data));
    }

    /** @test */
    public function 已登录管理员可以删除菜单()
    {
        // 创建管理员和菜单
        $admin = Admin::factory()->create();
        $menu = Menu::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->deleteJson('/api/menus/' . $menu->id);

        $response->assertOk()
            ->assertJsonStructure([
                'code',
                'message',
                'data',
            ]);

        $this->assertDatabaseMissing('menus', ['id' => $menu->id]);
    }

    /** @test */
    public function 有子菜单的菜单不能被删除()
    {
        // 创建管理员和菜单
        $admin = Admin::factory()->create();
        $parentMenu = Menu::factory()->create();
        Menu::factory()->child($parentMenu->id, 1)->create();

        $response = $this->actingAs($admin, 'admin')
            ->deleteJson('/api/menus/' . $parentMenu->id);

        $response->assertStatus(500)
            ->assertJsonStructure([
                'code',
                'message',
                'data',
            ]);

        $this->assertDatabaseHas('menus', ['id' => $parentMenu->id]);
    }

    /** @test */
    public function 菜单标题和级别是必填项()
    {
        // 创建管理员
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->postJson('/api/menus', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'level', 'sort', 'hidden', 'keep_alive']);
    }
}