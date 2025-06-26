<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Role;

test('未登录用户不能访问角色管理接口', function () {
    // 尝试获取角色列表
    $response = $this->get('/api/roles');
    $response->assertStatus(401);

    // 尝试创建角色
    $response = $this->post('/api/roles', [
        'name' => 'test role',
        'description' => 'test description',
    ]);
    $response->assertStatus(401);
});

test('已登录管理员可以获取角色列表', function () {
    // 先登录
    $loginResponse = $this->post('/api/auth/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);
    $token = $loginResponse->json('data.access_token');

    // 获取角色列表
    $response = $this->withToken($token)->get('/api/roles');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'code',
            'message',
            'data' => [
                'items',
                'total',
                'current_page',
                'per_page',
                'last_page',
            ],
        ])
        ->assertJson([
            'status' => 'success',
            'code' => 200,
        ]);
});

test('已登录管理员可以创建角色', function () {
    // 先登录
    $loginResponse = $this->post('/api/auth/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);
    $token = $loginResponse->json('data.access_token');

    // 创建角色
    $response = $this->withToken($token)->post('/api/roles', [
        'name' => 'test role',
        'description' => 'test description',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'code',
            'message',
            'data' => [
                'id',
                'name',
                'description',
            ],
        ])
        ->assertJson([
            'status' => 'success',
            'code' => 200,
            'data' => [
                'name' => 'test role',
                'description' => 'test description',
            ],
        ]);

    // 验证数据库中是否创建成功
    $this->assertDatabaseHas('roles', [
        'name' => 'test role',
        'description' => 'test description',
    ]);
});

test('已登录管理员可以更新角色', function () {
    // 先登录
    $loginResponse = $this->post('/api/auth/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);
    $token = $loginResponse->json('data.access_token');

    // 创建一个角色
    $role = Role::create([
        'name' => 'test role',
        'description' => 'test description',
    ]);

    // 更新角色
    $response = $this->withToken($token)->put("/api/roles/{$role->id}", [
        'name' => 'updated role',
        'description' => 'updated description',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'code',
            'message',
            'data' => [
                'id',
                'name',
                'description',
            ],
        ])
        ->assertJson([
            'status' => 'success',
            'code' => 200,
            'data' => [
                'name' => 'updated role',
                'description' => 'updated description',
            ],
        ]);

    // 验证数据库中是否更新成功
    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'updated role',
        'description' => 'updated description',
    ]);
});

test('已登录管理员可以删除未使用的角色', function () {
    // 先登录
    $loginResponse = $this->post('/api/auth/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);
    $token = $loginResponse->json('data.access_token');

    // 创建一个角色
    $role = Role::create([
        'name' => 'test role',
        'description' => 'test description',
    ]);

    // 删除角色
    $response = $this->withToken($token)->delete("/api/roles/{$role->id}");

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'code' => 200,
            'message' => '角色删除成功',
        ]);

    // 验证数据库中是否删除成功
    $this->assertDatabaseMissing('roles', [
        'id' => $role->id,
    ]);
});

test('角色名称必须唯一', function () {
    // 先登录
    $loginResponse = $this->post('/api/auth/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);
    $token = $loginResponse->json('data.access_token');

    // 创建第一个角色
    $this->withToken($token)->post('/api/roles', [
        'name' => 'test role',
        'description' => 'test description',
    ]);

    // 尝试创建同名角色
    $response = $this->withToken($token)->post('/api/roles', [
        'name' => 'test role',
        'description' => 'another description',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'status' => 'error',
            'code' => 422,
        ]);
});

test('不能删除正在使用的角色', function () {
    // 先登录
    $loginResponse = $this->post('/api/auth/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);
    $token = $loginResponse->json('data.access_token');

    // 获取超级管理员角色（已经与 admin 用户关联）
    $role = Role::where('name', 'super-admin')->first();

    // 尝试删除角色
    $response = $this->withToken($token)->delete("/api/roles/{$role->id}");

    $response->assertStatus(400)
        ->assertJson([
            'status' => 'error',
            'message' => '该角色下还有管理员，无法删除',
        ]);

    // 验证数据库中角色是否仍然存在
    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
    ]);
}); 