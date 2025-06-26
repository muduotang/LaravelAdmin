<?php

namespace App\Services;

use App\Models\Admin;
use App\Exceptions\BusinessException;
use App\Traits\AdminOperationLoggable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    use AdminOperationLoggable;

    /**
     * 管理员登录
     *
     * @param array $credentials
     * @param string $ip
     * @param string $userAgent
     * @return array
     * @throws BusinessException
     */
    public function login(array $credentials, string $ip, string $userAgent): array
    {
        // 尝试登录
        if (!$token = Auth::guard('admin')->attempt($credentials)) {
            throw new BusinessException('用户名或密码错误', 401);
        }

        // 记录登录日志
        $this->recordLoginLog($ip, $userAgent);

        // 返回令牌信息
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('admin')->factory()->getTTL() * 60,
        ];
    }

    /**
     * 获取当前用户信息
     *
     * @return array
     */
    public function getCurrentUser(): array
    {
        $user = Auth::guard('admin')->user();

        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'nick_name' => $user->nick_name,
            'icon' => $user->icon,
            'roles' => $user->roles()->get(['roles.id', 'roles.name', 'roles.description']),
        ];
    }

    /**
     * 刷新令牌
     *
     * @return array
     */
    public function refreshToken(): array
    {
        $token = Auth::guard('admin')->refresh();

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('admin')->factory()->getTTL() * 60,
        ];
    }

    /**
     * 退出登录
     *
     * @param string $ip
     * @param string $userAgent
     * @return void
     */
    public function logout(string $ip, string $userAgent): void
    {
        // 记录退出日志
        $this->recordLogoutLog($ip, $userAgent);
        
        // 执行退出操作
        Auth::guard('admin')->logout();
    }

    /**
     * 更新用户信息
     *
     * @param array $data
     * @param string $ip
     * @param string $userAgent
     * @return array
     * @throws BusinessException
     */
    public function updateProfile(array $data, string $ip, string $userAgent): array
    {
        $user = Auth::guard('admin')->user();

        // 如果提供了新密码，则验证旧密码并更新密码
        if (isset($data['old_password'])) {
            if (!Hash::check($data['old_password'], $user->password)) {
                throw new BusinessException('原密码错误', 422);
            }
            $data['password'] = Hash::make($data['new_password']);
            unset($data['old_password'], $data['new_password'], $data['new_password_confirmation']);
        }

        // 更新用户数据
        $user->update($data);

        // 记录操作日志
        $this->recordProfileUpdateLog(array_keys($data), $ip, $userAgent);

        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'nick_name' => $user->nick_name,
            'icon' => $user->icon,
        ];
    }
} 