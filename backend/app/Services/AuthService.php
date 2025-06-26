<?php

namespace App\Services;

use App\Models\Admin;
use App\Traits\AdminOperationLoggable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    use AdminOperationLoggable;

    /**
     * 管理员登录
     *
     * @param array $data
     * @return string
     */
    public function login(array $data): string
    {
        $admin = Admin::where('username', $data['username'])->first();

        if (!$admin || !Hash::check($data['password'], $admin->password)) {
            throw new \Exception('用户名或密码错误');
        }

        if ($admin->status === 0) {
            throw new \Exception('账号已被禁用');
        }

        $token = Auth::guard('admin')->login($admin);

        $this->recordLoginLog(request()->ip(), request()->userAgent());

        return $token;
    }

    /**
     * 管理员退出
     *
     * @return void
     */
    public function logout(): void
    {
        $this->recordLogoutLog(request()->ip(), request()->userAgent());
        Auth::guard('admin')->logout();
    }

    /**
     * 获取当前管理员信息
     *
     * @return Admin
     */
    public function getCurrentAdmin(): Admin
    {
        return Auth::guard('admin')->user();
    }

    /**
     * 更新个人信息
     *
     * @param array $data
     * @return Admin
     */
    public function updateProfile(array $data): Admin
    {
        $admin = Auth::guard('admin')->user();
        
        $admin->update($data);
        
        $this->recordProfileUpdateLog(
            $data,
            request()->ip(),
            request()->userAgent()
        );
        
        return $admin;
    }
} 