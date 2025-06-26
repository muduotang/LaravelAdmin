<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Resource;
use App\Models\ResourceCategory;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private ResourceCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        // 创建管理员
        $this->admin = Admin::factory()->create();
        
        // 创建资源分类
        $this->category = ResourceCategory::create([
            'name' => '测试分类',
            'sort' => 0,
        ]);
    }

    /** @test */
    public function 未登录用户不能访问资源接口()
    {
        $response = $this->getJson('/api/resources');
        $response->assertUnauthorized();
    }

    /** @test */
    public function 管理员可以获取资源列表()
    {
        // 创建测试资源
        Resource::create([
            'category_id' => $this->category->id,
            'name' => '测试资源',
            'route_name' => 'test.index',
            'description' => '测试描述',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->getJson('/api/resources');

        $response->assertOk()
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'category_id',
                        'name',
                        'route_name',
                        'description',
                        'created_at',
                        'updated_at',
                        'category' => [
                            'id',
                            'name',
                            'sort',
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function 管理员可以创建资源()
    {
        $data = [
            'category_id' => $this->category->id,
            'name' => '新资源',
            'route_name' => 'test.create',
            'description' => '新资源描述',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->postJson('/api/resources', $data);

        $response->assertOk()
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    'id',
                    'category_id',
                    'name',
                    'route_name',
                    'description',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('resources', $data);
    }

    /** @test */
    public function 创建资源时路由名称必须唯一()
    {
        // 创建已存在的资源
        Resource::create([
            'category_id' => $this->category->id,
            'name' => '已存在的资源',
            'route_name' => 'test.exists',
            'description' => '测试描述',
        ]);

        $data = [
            'category_id' => $this->category->id,
            'name' => '新资源',
            'route_name' => 'test.exists', // 使用已存在的路由名称
            'description' => '新资源描述',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->postJson('/api/resources', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['route_name']);
    }

    /** @test */
    public function 管理员可以更新资源()
    {
        $resource = Resource::create([
            'category_id' => $this->category->id,
            'name' => '原资源',
            'route_name' => 'test.old',
            'description' => '原描述',
        ]);

        $data = [
            'category_id' => $this->category->id,
            'name' => '更新后的资源',
            'route_name' => 'test.new',
            'description' => '更新后的描述',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->putJson("/api/resources/{$resource->id}", $data);

        $response->assertOk();
        $this->assertDatabaseHas('resources', $data);
    }

    /** @test */
    public function 管理员可以删除未使用的资源()
    {
        $resource = Resource::create([
            'category_id' => $this->category->id,
            'name' => '待删除资源',
            'route_name' => 'test.delete',
            'description' => '待删除描述',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->deleteJson("/api/resources/{$resource->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('resources', ['id' => $resource->id]);
    }

    /** @test */
    public function 不能删除已被角色使用的资源()
    {
        // 创建角色
        $role = Role::create([
            'name' => '测试角色',
            'description' => '测试角色描述',
        ]);

        // 创建资源并分配给角色
        $resource = Resource::create([
            'category_id' => $this->category->id,
            'name' => '已分配资源',
            'route_name' => 'test.assigned',
            'description' => '已分配描述',
        ]);

        $role->resources()->attach($resource->id);

        $response = $this->actingAs($this->admin, 'admin')
            ->deleteJson("/api/resources/{$resource->id}");

        $response->assertStatus(500)
            ->assertJson([
                'code' => 500,
                'message' => '该资源已被角色使用，不能删除',
            ]);

        $this->assertDatabaseHas('resources', ['id' => $resource->id]);
    }

    /** @test */
    public function 创建资源时分类必须存在()
    {
        $data = [
            'category_id' => 999, // 不存在的分类ID
            'name' => '新资源',
            'route_name' => 'test.category',
            'description' => '新资源描述',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->postJson('/api/resources', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    }
} 