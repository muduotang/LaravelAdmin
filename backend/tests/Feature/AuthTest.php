<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create([
            'username' => 'admin',
            'password' => bcrypt('password'),
        ]);
    }

    /** @test */
    public function 管理员可以使用正确的凭证登录()
    {
        $response = $this->postJson('/api/auth/login', [
            'username' => 'admin',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'code',
                'message',
                'data' => ['token'],
            ]);
    }

    /** @test */
    public function 管理员不能使用错误的凭证登录()
    {
        $response = $this->postJson('/api/auth/login', [
            'username' => 'admin',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'code' => 500,
                'message' => '用户名或密码错误',
            ]);
    }

    /** @test */
    public function 登录请求必须包含用户名和密码()
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username', 'password']);
    }

    /** @test */
    public function 已登录管理员可以获取个人信息()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson('/api/auth/me');

        $response->assertOk()
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    'id',
                    'username',
                    'email',
                    'nick_name',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    /** @test */
    public function 未登录用户不能访问受保护的路由()
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertUnauthorized();
    }

    /** @test */
    public function 已登录管理员可以退出登录()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson('/api/auth/logout');

        $response->assertOk();

        // 验证用户已退出
        $this->getJson('/api/auth/me')->assertUnauthorized();
    }

    /** @test */
    public function 已登录管理员可以更新个人信息()
    {
        $data = [
            'nick_name' => '新昵称',
            'email' => 'new@example.com',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->putJson('/api/auth/me', $data);

        $response->assertOk()
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    'id',
                    'username',
                    'email',
                    'nick_name',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('admins', [
            'id' => $this->admin->id,
            'nick_name' => '新昵称',
            'email' => 'new@example.com',
        ]);
    }

    /** @test */
    public function 测试更新个人信息()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->putJson('/api/auth/me', [
            'email' => 'newemail@example.com',
            'nick_name' => 'New Nick',
            'icon' => 'http://example.com/new-icon.png'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'code' => 200
                ]);

        $this->assertDatabaseHas('admins', [
            'id' => $admin->id,
            'email' => 'newemail@example.com',
            'nick_name' => 'New Nick',
            'icon' => 'http://example.com/new-icon.png'
        ]);
    }

    /** @test */
    public function 测试刷新令牌接口()
    {
        // 首先登录获取真实的JWT令牌
        $admin = Admin::factory()->create([
            'username' => 'testuser',
            'password' => bcrypt('password')
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'username' => 'testuser',
            'password' => 'password'
        ]);

        $loginResponse->assertOk();
        $token = $loginResponse->json('data.token');

        // 使用获取到的令牌进行刷新测试
        $response = $this->postJson('/api/auth/refresh', [], [
            'Authorization' => 'Bearer ' . $token
        ]);
        
        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'code' => 200,
                    'message' => '令牌刷新成功'
                ])
                ->assertJsonStructure([
                    'data' => ['token']
                ]);
    }

    /** @test */
    public function 测试登录时用户名验证()
    {
        $response = $this->postJson('/api/auth/login', [
            'password' => 'password123'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['username']);
    }

    /** @test */
    public function 测试登录时密码验证()
    {
        $response = $this->postJson('/api/auth/login', [
            'username' => 'testuser'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function 测试登录时用户名和密码都为空()
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['username', 'password']);
    }

    /** @test */
    public function 测试登录时用户被禁用()
    {
        $admin = Admin::factory()->create([
            'username' => 'testuser',
            'password' => bcrypt('password123'),
            'status' => 0 // 禁用状态
        ]);

        $response = $this->postJson('/api/auth/login', [
            'username' => 'testuser',
            'password' => 'password123'
        ]);

        $response->assertStatus(500)
                ->assertJson([
                    'status' => 'error',
                    'message' => '账号已被禁用'
                ]);
    }

    /** @test */
    public function 测试更新个人信息时邮箱格式验证()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->putJson('/api/auth/me', [
            'email' => 'invalid-email-format'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function 测试更新个人信息时邮箱长度验证()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->putJson('/api/auth/me', [
            'email' => str_repeat('a', 250) . '@test.com' // 超过255字符
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function 测试更新个人信息时邮箱唯一性验证()
    {
        $admin1 = Admin::factory()->create(['email' => 'existing@example.com']);
        $admin2 = Admin::factory()->create();
        $this->actingAs($admin2, 'admin');

        $response = $this->putJson('/api/auth/me', [
            'email' => 'existing@example.com'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function 测试更新个人信息时昵称长度验证()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->putJson('/api/auth/me', [
            'nick_name' => str_repeat('a', 51) // 超过50字符
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['nick_name']);
    }

    /** @test */
    public function 测试更新个人信息时头像地址长度验证()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->putJson('/api/auth/me', [
            'icon' => str_repeat('http://example.com/', 15) // 超过255字符
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['icon']);
    }

    /** @test */
    public function 测试更新密码时原密码验证()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->putJson('/api/auth/me', [
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['old_password']);
    }

    /** @test */
    public function 测试更新密码时新密码验证()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->putJson('/api/auth/me', [
            'old_password' => 'password',
            'new_password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['new_password']);
    }

    /** @test */
    public function 测试更新密码时新密码长度验证()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->putJson('/api/auth/me', [
            'old_password' => 'password',
            'new_password' => '12345', // 少于6位
            'new_password_confirmation' => '12345'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['new_password']);
    }

    /** @test */
    public function 测试更新密码时密码确认验证()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->putJson('/api/auth/me', [
            'old_password' => 'password',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'differentpassword'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['new_password']);
    }

    /** @test */
    public function 测试更新密码时确认密码必填验证()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->putJson('/api/auth/me', [
            'old_password' => 'password',
            'new_password' => 'newpassword123'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['new_password_confirmation']);
    }

    /** @test */
    public function 测试更新密码时原密码错误()
    {
        $admin = Admin::factory()->create([
            'password' => bcrypt('correctpassword')
        ]);
        $this->actingAs($admin, 'admin');

        $response = $this->putJson('/api/auth/me', [
            'old_password' => 'wrongpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(500)
                ->assertJson([
                    'status' => 'error',
                    'message' => '原密码错误'
                ]);
    }

    /** @test */
    public function 测试成功更新密码()
    {
        $admin = Admin::factory()->create([
            'password' => bcrypt('oldpassword')
        ]);
        $this->actingAs($admin, 'admin');

        $response = $this->putJson('/api/auth/me', [
            'old_password' => 'oldpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'code' => 200
                ]);

        // 验证密码已更新
        $admin->refresh();
        $this->assertTrue(Hash::check('newpassword123', $admin->password));
    }

    /** @test */
    public function 测试只更新部分字段()
    {
        $admin = Admin::factory()->create([
            'email' => 'old@example.com',
            'nick_name' => 'Old Nick'
        ]);
        $this->actingAs($admin, 'admin');

        $response = $this->putJson('/api/auth/me', [
            'nick_name' => 'New Nick'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('admins', [
            'id' => $admin->id,
            'email' => 'old@example.com', // 未更改
            'nick_name' => 'New Nick' // 已更改
        ]);
    }

    /** @test */
    public function 测试清空可选字段()
    {
        $admin = Admin::factory()->create([
            'email' => 'test@example.com',
            'nick_name' => 'Test Nick',
            'icon' => 'http://example.com/icon.png'
        ]);
        $this->actingAs($admin, 'admin');

        $response = $this->putJson('/api/auth/me', [
            'nick_name' => null,
            'icon' => null
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'code' => 200
                ]);

        $this->assertDatabaseHas('admins', [
            'id' => $admin->id,
            'nick_name' => null,
            'icon' => null
        ]);
    }

    /** @test */
    public function 测试使用相同邮箱更新()
    {
        $admin = Admin::factory()->create([
            'email' => 'test@example.com'
        ]);
        $this->actingAs($admin, 'admin');

        $response = $this->putJson('/api/auth/me', [
            'email' => 'test@example.com',
            'nick_name' => 'Updated Nick'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('admins', [
            'id' => $admin->id,
            'email' => 'test@example.com',
            'nick_name' => 'Updated Nick'
        ]);
    }
}
