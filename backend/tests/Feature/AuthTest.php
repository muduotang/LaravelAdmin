<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
