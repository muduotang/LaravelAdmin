<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 创建测试用的超级管理员
        $this->admin = Admin::factory()->create([
            'username' => 'admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'status' => 1
        ]);
        
        // 创建测试角色
        $this->role = Role::factory()->create([
            'name' => 'test_role',
            'description' => '测试角色'
        ]);
    }

    /** @test */
    public function 已登录管理员可以获取管理员列表()
    {
        // 创建一些测试数据
        Admin::factory()->count(5)->create();
        
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson('/api/admins');
            
        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'username',
                            'email',
                            'nick_name',
                            'note',
                            'status',
                            'created_at',
                            'updated_at',
                            'roles'
                        ]
                    ],
                    'current_page',
                    'per_page',
                    'total'
                ]
            ]);
    }

    /** @test */
    public function 已登录管理员可以创建新管理员()
    {
        $adminData = [
            'username' => 'newadmin',
            'email' => 'newadmin@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nick_name' => '新管理员',
            'note' => '这是一个新管理员',
            'status' => 1,
            'role_ids' => [$this->role->id]
        ];
        
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson('/api/admins', $adminData);
            
        $response->assertStatus(201)
            ->assertJson([
                'code' => 201,
                'message' => '创建管理员成功'
            ]);
            
        $this->assertDatabaseHas('admins', [
            'username' => 'newadmin',
            'email' => 'newadmin@test.com',
            'nick_name' => '新管理员'
        ]);
        
        // 验证角色关联
        $newAdmin = Admin::where('username', 'newadmin')->first();
        $this->assertTrue($newAdmin->roles->contains($this->role));
    }

    /** @test */
    public function 创建管理员时用户名和邮箱是必填项()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson('/api/admins', []);
            
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username', 'email', 'password', 'nick_name', 'status']);
    }

    /** @test */
    public function 创建管理员时用户名必须唯一()
    {
        $existingAdmin = Admin::factory()->create(['username' => 'existing']);
        
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson('/api/admins', [
                'username' => 'existing',
                'email' => 'new@test.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'nick_name' => '新管理员',
                'status' => 1
            ]);
            
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username']);
    }

    /** @test */
    public function 创建管理员时邮箱必须唯一()
    {
        $existingAdmin = Admin::factory()->create(['email' => 'existing@test.com']);
        
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson('/api/admins', [
                'username' => 'newuser',
                'email' => 'existing@test.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'nick_name' => '新管理员',
                'status' => 1
            ]);
            
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function 已登录管理员可以查看管理员详情()
    {
        $testAdmin = Admin::factory()->create();
        $testAdmin->roles()->attach($this->role);
        
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson("/api/admins/{$testAdmin->id}");
            
        $response->assertStatus(200)
            ->assertJson([
                'code' => 200,
                'data' => [
                    'id' => $testAdmin->id,
                    'username' => $testAdmin->username,
                    'email' => $testAdmin->email
                ]
            ]);
    }

    /** @test */
    public function 已登录管理员可以更新管理员信息()
    {
        $testAdmin = Admin::factory()->create();
        
        $updateData = [
            'username' => 'updated_username',
            'email' => 'updated@test.com',
            'nick_name' => '更新的昵称',
            'note' => '更新的备注',
            'status' => 0
        ];
        
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson("/api/admins/{$testAdmin->id}", $updateData);
            
        $response->assertStatus(200)
            ->assertJson([
                'code' => 200,
                'message' => '更新管理员成功'
            ]);
            
        $this->assertDatabaseHas('admins', [
            'id' => $testAdmin->id,
            'username' => 'updated_username',
            'email' => 'updated@test.com',
            'nick_name' => '更新的昵称'
        ]);
    }

    /** @test */
    public function 已登录管理员可以删除其他管理员()
    {
        $testAdmin = Admin::factory()->create();
        
        $response = $this->actingAs($this->admin, 'admin')
            ->deleteJson("/api/admins/{$testAdmin->id}");
            
        $response->assertStatus(200)
            ->assertJson([
                'code' => 200,
                'message' => '删除管理员成功'
            ]);
            
        $this->assertDatabaseMissing('admins', [
            'id' => $testAdmin->id
        ]);
    }

    /** @test */
    public function 管理员不能删除自己()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->deleteJson("/api/admins/{$this->admin->id}");
            
        $response->assertStatus(400)
            ->assertJson([
                'code' => 400,
                'message' => '不能删除自己'
            ]);
            
        $this->assertDatabaseHas('admins', [
            'id' => $this->admin->id
        ]);
    }

    /** @test */
    public function 已登录管理员可以为其他管理员分配角色()
    {
        $testAdmin = Admin::factory()->create();
        $role2 = Role::factory()->create();
        
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson("/api/admins/{$testAdmin->id}/roles", [
                'role_ids' => [$this->role->id, $role2->id]
            ]);
            
        $response->assertStatus(200)
            ->assertJson([
                'code' => 200,
                'message' => '分配角色成功'
            ]);
            
        $testAdmin->refresh();
        $this->assertTrue($testAdmin->roles->contains($this->role));
        $this->assertTrue($testAdmin->roles->contains($role2));
    }

    /** @test */
    public function 已登录管理员可以重置其他管理员密码()
    {
        $testAdmin = Admin::factory()->create();
        
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson("/api/admins/{$testAdmin->id}/reset-password", [
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123'
            ]);
            
        $response->assertStatus(200)
            ->assertJson([
                'code' => 200,
                'message' => '重置密码成功'
            ]);
            
        $testAdmin->refresh();
        $this->assertTrue(Hash::check('newpassword123', $testAdmin->password));
    }

    /** @test */
    public function 已登录管理员可以更新其他管理员状态()
    {
        $testAdmin = Admin::factory()->create(['status' => 1]);
        
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson("/api/admins/{$testAdmin->id}/status", [
                'status' => 0
            ]);
            
        $response->assertStatus(200)
            ->assertJson([
                'code' => 200,
                'message' => '更新状态成功'
            ]);
            
        $testAdmin->refresh();
        $this->assertEquals(0, $testAdmin->status);
    }

    /** @test */
    public function 管理员不能禁用自己()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson("/api/admins/{$this->admin->id}/status", [
                'status' => 0
            ]);
            
        $response->assertStatus(400)
            ->assertJson([
                'code' => 400,
                'message' => '不能禁用自己'
            ]);
            
        $this->admin->refresh();
        $this->assertEquals(1, $this->admin->status);
    }

    /** @test */
    public function 可以根据关键词搜索管理员()
    {
        Admin::factory()->create(['username' => 'searchuser', 'nick_name' => '搜索用户']);
        Admin::factory()->create(['username' => 'otheruser', 'nick_name' => '其他用户']);
        
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson('/api/admins?keyword=search');
            
        $response->assertStatus(200);
        
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('searchuser', $data[0]['username']);
    }

    /** @test */
    public function 可以根据状态筛选管理员()
    {
        Admin::factory()->create(['status' => 1]);
        Admin::factory()->create(['status' => 0]);
        
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson('/api/admins?status=1');
            
        $response->assertStatus(200);
        
        $data = $response->json('data.data');
        foreach ($data as $admin) {
            $this->assertEquals(1, $admin['status']);
        }
    }

    /** @test */
    public function 未登录用户无法访问管理员接口()
    {
        $response = $this->getJson('/api/admins');
        $response->assertStatus(401);
    }

    /** @test */
    public function 管理员分配角色时角色ID必须存在()
    {
        $admin = Admin::factory()->create();
        $targetAdmin = Admin::factory()->create();
        
        $data = [
            'role_ids' => [999, 1000], // 不存在的角色ID
        ];

        $response = $this->actingAs($admin, 'admin')
            ->postJson("/api/admins/{$targetAdmin->id}/roles", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role_ids']);
    }

    /** @test */
    public function 管理员可以清空其他管理员的角色()
    {
        $admin = Admin::factory()->create();
        $targetAdmin = Admin::factory()->create();
        $roles = Role::factory()->count(2)->create();
        
        // 先分配角色
        $targetAdmin->roles()->sync($roles->pluck('id'));
        
        // 清空角色
        $response = $this->actingAs($admin, 'admin')
            ->postJson("/api/admins/{$targetAdmin->id}/roles", [
                'role_ids' => [],
            ]);

        $response->assertOk();
        
        // 验证角色已清空
        $this->assertEquals(0, $targetAdmin->fresh()->roles()->count());
    }

    /** @test */
    public function 可以根据角色筛选管理员()
    {
        $role1 = Role::factory()->create(['name' => 'role1']);
        $role2 = Role::factory()->create(['name' => 'role2']);
        
        $admin1 = Admin::factory()->create();
        $admin2 = Admin::factory()->create();
        $admin3 = Admin::factory()->create();
        
        $admin1->roles()->attach($role1);
        $admin2->roles()->attach($role2);
        // admin3 没有角色
        
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson("/api/admins?role_id={$role1->id}");
            
        $response->assertStatus(200);
        
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals($admin1->id, $data[0]['id']);
    }

    /** @test */
    public function 管理员列表支持分页()
    {
        Admin::factory()->count(20)->create();
        
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson('/api/admins?per_page=5&page=2');
            
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'data',
                    'current_page',
                    'per_page',
                    'total',
                    'last_page'
                ]
            ]);
            
        $this->assertEquals(2, $response->json('data.current_page'));
        $this->assertEquals(5, $response->json('data.per_page'));
    }

    /** @test */
    public function 创建管理员时用户名格式必须正确()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson('/api/admins', [
                'username' => 'invalid-username!', // 包含非法字符
                'email' => 'test@test.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'nick_name' => '测试用户',
                'status' => 1
            ]);
            
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username']);
    }

    /** @test */
    public function 创建管理员时字段长度验证()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson('/api/admins', [
                'username' => str_repeat('a', 51), // 超过50字符
                'email' => str_repeat('a', 92) . '@test.com', // 超过100字符 (92+9=101)
                'password' => '12345', // 少于6字符
                'password_confirmation' => '12345',
                'nick_name' => str_repeat('测', 51), // 超过50字符
                'note' => str_repeat('备注', 251), // 超过500字符
                'icon' => str_repeat('http://example.com/', 30), // 超过500字符 (18*30=540)
                'status' => 1
            ]);
            
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'username', 'email', 'password', 'nick_name', 'note', 'icon'
            ]);
    }

    /** @test */
    public function 创建管理员时邮箱格式必须正确()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson('/api/admins', [
                'username' => 'testuser',
                'email' => 'invalid-email', // 无效邮箱格式
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'nick_name' => '测试用户',
                'status' => 1
            ]);
            
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function 创建管理员时状态值必须有效()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson('/api/admins', [
                'username' => 'testuser',
                'email' => 'test@test.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'nick_name' => '测试用户',
                'status' => 2 // 无效状态值
            ]);
            
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function 创建管理员时密码确认必须一致()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson('/api/admins', [
                'username' => 'testuser',
                'email' => 'test@test.com',
                'password' => 'password123',
                'password_confirmation' => 'different_password', // 密码确认不一致
                'nick_name' => '测试用户',
                'status' => 1
            ]);
            
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function 更新管理员时密码是可选的()
    {
        $testAdmin = Admin::factory()->create();
        $originalPassword = $testAdmin->password;
        
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson("/api/admins/{$testAdmin->id}", [
                'username' => 'updated_username',
                'email' => 'updated@test.com',
                'nick_name' => '更新的昵称',
                'status' => 1
                // 不提供密码
            ]);
            
        $response->assertStatus(200);
        
        $testAdmin->refresh();
        $this->assertEquals($originalPassword, $testAdmin->password); // 密码未改变
    }

    /** @test */
    public function 更新管理员时可以修改密码()
    {
        $testAdmin = Admin::factory()->create();
        $originalPassword = $testAdmin->password;
        
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson("/api/admins/{$testAdmin->id}", [
                'username' => 'updated_username',
                'email' => 'updated@test.com',
                'nick_name' => '更新的昵称',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
                'status' => 1
            ]);
            
        $response->assertStatus(200);
        
        $testAdmin->refresh();
        $this->assertNotEquals($originalPassword, $testAdmin->password); // 密码已改变
        $this->assertTrue(Hash::check('newpassword123', $testAdmin->password));
    }

    /** @test */
    public function 重置密码时密码长度必须符合要求()
    {
        $testAdmin = Admin::factory()->create();
        
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson("/api/admins/{$testAdmin->id}/reset-password", [
                'password' => '12345', // 少于6字符
                'password_confirmation' => '12345'
            ]);
            
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function 重置密码时确认密码必须一致()
    {
        $testAdmin = Admin::factory()->create();
        
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson("/api/admins/{$testAdmin->id}/reset-password", [
                'password' => 'password123',
                'password_confirmation' => 'different_password'
            ]);
            
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function 更新状态时状态值必须有效()
    {
        $testAdmin = Admin::factory()->create();
        
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson("/api/admins/{$testAdmin->id}/status", [
                'status' => 2 // 无效状态值
            ]);
            
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function 访问不存在的管理员返回404()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson('/api/admins/99999');
            
        $response->assertStatus(404);
    }

    /** @test */
    public function 更新不存在的管理员返回404()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson('/api/admins/99999', [
                'username' => 'test',
                'email' => 'test@test.com',
                'nick_name' => '测试',
                'status' => 1
            ]);
            
        $response->assertStatus(404);
    }

    /** @test */
    public function 删除不存在的管理员返回404()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->deleteJson('/api/admins/99999');
            
        $response->assertStatus(404);
    }

    /** @test */
    public function 分配角色时role_ids字段必须是数组()
    {
        $testAdmin = Admin::factory()->create();
        
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson("/api/admins/{$testAdmin->id}/roles", [
                'role_ids' => 'not_an_array'
            ]);
            
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role_ids']);
    }

    /** @test */
    public function 分配角色时可以传递空数组清空角色()
    {
        $testAdmin = Admin::factory()->create();
        $testAdmin->roles()->attach($this->role);
        
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson("/api/admins/{$testAdmin->id}/roles", [
                'role_ids' => []
            ]);
            
        $response->assertStatus(200);
        
        $testAdmin->refresh();
        $this->assertEquals(0, $testAdmin->roles()->count());
    }

    /** @test */
    public function 分配不存在角色时返回验证错误()
    {
        $testAdmin = Admin::factory()->create();
        
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson("/api/admins/{$testAdmin->id}/roles", [
                'role_ids' => [99999] // 不存在的角色ID
            ]);
            
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role_ids']);
    }

    /** @test */
    public function 管理员列表可以同时使用多个筛选条件()
    {
        $role = Role::factory()->create();
        $admin1 = Admin::factory()->create([
            'username' => 'searchuser1',
            'status' => 1
        ]);
        $admin2 = Admin::factory()->create([
            'username' => 'searchuser2', 
            'status' => 0
        ]);
        $admin3 = Admin::factory()->create([
            'username' => 'otheruser',
            'status' => 1
        ]);
        
        $admin1->roles()->attach($role);
        
        // 同时使用关键词、状态和角色筛选
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson("/api/admins?keyword=search&status=1&role_id={$role->id}");
            
        $response->assertStatus(200);
        
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals($admin1->id, $data[0]['id']);
    }

    /** @test */
    public function 创建管理员时可以不分配角色()
    {
        $adminData = [
            'username' => 'noroleadmin',
            'email' => 'norole@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nick_name' => '无角色管理员',
            'status' => 1
            // 不提供 role_ids
        ];
        
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson('/api/admins', $adminData);
            
        $response->assertStatus(201);
        
        $newAdmin = Admin::where('username', 'noroleadmin')->first();
        $this->assertEquals(0, $newAdmin->roles()->count());
    }
}