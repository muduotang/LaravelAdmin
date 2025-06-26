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
}