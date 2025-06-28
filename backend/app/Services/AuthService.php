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
     * 刷新令牌
     *
     * @return array
     */
    public function refreshToken(): array
    {
        $token = Auth::guard('admin')->refresh();
        return ['token' => $token];
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
        
        // 如果包含密码相关字段，需要验证原密码
        if (isset($data['old_password']) && isset($data['new_password'])) {
            if (!Hash::check($data['old_password'], $admin->password)) {
                throw new \Exception('原密码错误');
            }
            
            // 更新密码
            $data['password'] = Hash::make($data['new_password']);
            
            // 移除不需要保存的字段
            unset($data['old_password'], $data['new_password'], $data['new_password_confirmation']);
        }
        
        // 处理可空字段，允许显式设置为null
        $allowedNullFields = ['nick_name', 'icon', 'note'];
        $updateData = [];
        
        foreach ($data as $key => $value) {
            if ($value !== null || in_array($key, $allowedNullFields)) {
                $updateData[$key] = $value;
            }
        }
        
        $admin->update($updateData);
        
        $this->recordProfileUpdateLog(
            $data,
            request()->ip(),
            request()->userAgent()
        );
        
        return $admin;
    }
}