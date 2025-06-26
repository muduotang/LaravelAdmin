<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

uses(TestCase::class, WithFaker::class)
    ->beforeEach(function () {
        // 运行数据库迁移和填充
        $this->artisan('migrate:fresh');
        $this->artisan('db:seed');
    })
    ->in(__DIR__);

test('管理员可以使用正确的凭证登录', function () {
    $response = $this->post('/api/auth/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'code',
            'message',
            'data' => [
                'access_token',
            ],
        ])
        ->assertJson([
            'status' => 'success',
            'code' => 200,
        ]);

    expect($response->json('data.access_token'))->toBeString()->not->toBeEmpty();
});

test('管理员不能使用错误的凭证登录', function () {
    $response = $this->post('/api/auth/login', [
        'username' => 'admin',
        'password' => 'wrong_password',
    ]);

    $response->assertStatus(401)
        ->assertJsonStructure([
            'status',
            'code',
            'message',
        ])
        ->assertJson([
            'status' => 'error',
            'code' => 401,
        ]);
});

test('登录请求必须包含用户名和密码', function () {
    // 测试缺少用户名
    $response = $this->post('/api/auth/login', [
        'password' => 'admin123',
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'status',
            'code',
            'message',
            'data' => [
                'errors',
            ],
        ]);

    // 测试缺少密码
    $response = $this->post('/api/auth/login', [
        'username' => 'admin',
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'status',
            'code',
            'message',
            'data' => [
                'errors',
            ],
        ]);
});

test('已登录管理员可以获取个人信息', function () {
    // 先登录获取 token
    $loginResponse = $this->post('/api/auth/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);

    $token = $loginResponse->json('data.access_token');

    // 使用 token 获取用户信息
    $response = $this->withToken($token)->get('/api/auth/me');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'code',
            'message',
            'data' => [
                'id',
                'username',
                'email',
                'nick_name',
                'icon',
                'roles',
            ],
        ])
        ->assertJson([
            'status' => 'success',
            'code' => 200,
        ]);
});

test('未登录用户不能访问受保护的路由', function () {
    $response = $this->get('/api/auth/me');
    
    $response->assertStatus(401)
        ->assertJsonStructure([
            'status',
            'code',
            'message',
        ])
        ->assertJson([
            'status' => 'error',
            'code' => 401,
            'message' => '未经授权',
        ]);
});

test('已登录管理员可以刷新令牌', function () {
    // 先登录获取 token
    $loginResponse = $this->post('/api/auth/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);

    $token = $loginResponse->json('data.access_token');

    // 刷新 token
    $response = $this->withToken($token)->post('/api/auth/refresh');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'code',
            'message',
            'data' => [
                'access_token',
            ],
        ])
        ->assertJson([
            'status' => 'success',
            'code' => 200,
        ]);

    expect($response->json('data.access_token'))
        ->toBeString()
        ->not->toBeEmpty()
        ->not->toBe($token);
});

test('已登录管理员可以退出登录', function () {
    // 先登录获取 token
    $loginResponse = $this->post('/api/auth/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);

    $token = $loginResponse->json('data.access_token');

    // 退出登录
    $response = $this->withToken($token)->post('/api/auth/logout');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'code',
            'message',
        ])
        ->assertJson([
            'status' => 'success',
            'code' => 200,
        ]);

    // 验证使用已登出的 token 无法访问受保护的路由
    $meResponse = $this->withToken($token)->get('/api/auth/me');
    $meResponse->assertStatus(401)
        ->assertJsonStructure([
            'status',
            'code',
            'message',
        ])
        ->assertJson([
            'status' => 'error',
            'code' => 401,
            'message' => '未经授权',
        ]);
});

test('已登录管理员可以更新个人信息', function () {
    // 先登录获取 token
    $loginResponse = $this->post('/api/auth/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);

    $token = $loginResponse->json('data.access_token');

    // 更新个人信息
    $response = $this->withToken($token)->put('/api/auth/me', [
        'email' => 'new.admin@example.com',
        'nick_name' => '新管理员',
        'icon' => 'https://example.com/new-avatar.jpg',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'code',
            'message',
            'data' => [
                'id',
                'username',
                'email',
                'nick_name',
                'icon',
            ],
        ])
        ->assertJson([
            'status' => 'success',
            'code' => 200,
            'data' => [
                'email' => 'new.admin@example.com',
                'nick_name' => '新管理员',
                'icon' => 'https://example.com/new-avatar.jpg',
            ],
        ]);
});

test('已登录管理员可以修改密码', function () {
    // 先登录获取 token
    $loginResponse = $this->post('/api/auth/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);

    $token = $loginResponse->json('data.access_token');

    // 修改密码
    $response = $this->withToken($token)->put('/api/auth/me', [
        'old_password' => 'admin123',
        'new_password' => 'newpassword123',
        'new_password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'code' => 200,
        ]);

    // 使用新密码登录
    $newLoginResponse = $this->post('/api/auth/login', [
        'username' => 'admin',
        'password' => 'newpassword123',
    ]);

    $newLoginResponse->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'code' => 200,
        ]);
});

test('更新个人信息时验证规则生效', function () {
    // 先登录获取 token
    $loginResponse = $this->post('/api/auth/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);

    $token = $loginResponse->json('data.access_token');

    // 测试邮箱格式验证
    $response = $this->withToken($token)->put('/api/auth/me', [
        'email' => 'invalid-email',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'status' => 'error',
            'code' => 422,
        ]);

    // 测试密码验证
    $response = $this->withToken($token)->put('/api/auth/me', [
        'new_password' => 'short',
        'new_password_confirmation' => 'short',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'status' => 'error',
            'code' => 422,
        ]);

    // 测试密码确认
    $response = $this->withToken($token)->put('/api/auth/me', [
        'old_password' => 'admin123',
        'new_password' => 'newpassword123',
        'new_password_confirmation' => 'different',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'status' => 'error',
            'code' => 422,
        ]);
});
