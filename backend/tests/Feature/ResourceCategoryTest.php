<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Resource;
use App\Models\ResourceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceCategoryTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create();
    }

    /** @test */
    public function 未登录用户不能访问资源分类接口()
    {
        $response = $this->getJson('/api/resource-categories');
        $response->assertUnauthorized();
    }

    /** @test */
    public function 管理员可以获取资源分类列表()
    {
        ResourceCategory::create([
            'name' => '测试分类',
            'sort' => 0,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->getJson('/api/resource-categories');

        $response->assertOk()
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'sort',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    /** @test */
    public function 管理员可以创建资源分类()
    {
        $data = [
            'name' => '新分类',
            'sort' => 1,
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->postJson('/api/resource-categories', $data);

        $response->assertOk()
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    'id',
                    'name',
                    'sort',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('resource_categories', $data);
    }

    /** @test */
    public function 创建资源分类时名称必须唯一()
    {
        // 创建已存在的分类
        ResourceCategory::create([
            'name' => '已存在的分类',
            'sort' => 0,
        ]);

        $data = [
            'name' => '已存在的分类',
            'sort' => 1,
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->postJson('/api/resource-categories', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function 管理员可以更新资源分类()
    {
        $category = ResourceCategory::create([
            'name' => '原分类',
            'sort' => 0,
        ]);

        $data = [
            'name' => '更新后的分类',
            'sort' => 1,
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->putJson("/api/resource-categories/{$category->id}", $data);

        $response->assertOk();
        $this->assertDatabaseHas('resource_categories', $data);
    }

    /** @test */
    public function 管理员可以删除未使用的资源分类()
    {
        $category = ResourceCategory::create([
            'name' => '待删除分类',
            'sort' => 0,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->deleteJson("/api/resource-categories/{$category->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('resource_categories', ['id' => $category->id]);
    }

    /** @test */
    public function 不能删除包含资源的分类()
    {
        $category = ResourceCategory::create([
            'name' => '包含资源的分类',
            'sort' => 0,
        ]);

        // 创建资源并关联到分类
        Resource::create([
            'category_id' => $category->id,
            'name' => '测试资源',
            'route_name' => 'test.resource',
            'description' => '测试描述',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->deleteJson("/api/resource-categories/{$category->id}");

        $response->assertStatus(500)
            ->assertJson([
                'code' => 500,
                'message' => '该分类下有资源，不能删除',
            ]);

        $this->assertDatabaseHas('resource_categories', ['id' => $category->id]);
    }
} 