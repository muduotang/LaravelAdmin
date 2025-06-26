<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 创建超级管理员
        $superAdmin = Admin::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'email' => 'admin@example.com',
            'nick_name' => '超级管理员',
            'note' => '系统超级管理员，拥有所有权限',
            'status' => 1,
        ]);

        // 创建测试管理员
        $testAdmin = Admin::create([
            'username' => 'test',
            'password' => Hash::make('test123'),
            'email' => 'test@example.com',
            'nick_name' => '测试管理员',
            'note' => '用于测试的管理员账号',
            'status' => 1,
        ]);

        // 创建普通管理员
        $normalAdmin = Admin::create([
            'username' => 'user',
            'password' => Hash::make('user123'),
            'email' => 'user@example.com',
            'nick_name' => '普通管理员',
            'note' => '普通管理员账号',
            'status' => 1,
        ]);

        // 如果角色存在，分配角色
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $superAdmin->roles()->attach($superAdminRole);
        }

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $testAdmin->roles()->attach($adminRole);
        }

        $userRole = Role::where('name', 'user')->first();
        if ($userRole) {
            $normalAdmin->roles()->attach($userRole);
        }

        // 创建一些随机的管理员用于测试
        Admin::factory()->count(10)->create();
    }
}