<?php

namespace App\Traits;

use App\Models\AdminOperationLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

trait AdminOperationLoggable
{
    /**
     * 记录管理员操作日志
     *
     * @param string $operation 操作类型
     * @param array|null $data 操作数据
     * @param string|null $method 请求方法
     * @param string|null $path 请求路径
     * @param string|null $ip IP地址
     * @param string|null $userAgent 用户代理
     * @return void
     */
    protected function recordAdminOperation(
        string $operation,
        ?array $data = null,
        ?string $method = null,
        ?string $path = null,
        ?string $ip = null,
        ?string $userAgent = null
    ): void {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            return;
        }

        $request = request();
        $currentRoute = Route::current();
        
        AdminOperationLog::create([
            'admin_id' => $admin->id,
            'operation' => $operation,
            'method' => $method ?? $request->method(),
            'path' => $path ?? $request->path(),
            'route_name' => $currentRoute ? $currentRoute->getName() : null,
            'data' => $data ? json_encode($data, JSON_UNESCAPED_UNICODE) : null,
            'ip' => $ip ?? $request->ip(),
            'user_agent' => $userAgent ?? $request->userAgent(),
        ]);
    }

    /**
     * 记录登录日志
     *
     * @param string $ip
     * @param string $userAgent
     * @return void
     */
    protected function recordLoginLog(string $ip, string $userAgent): void
    {
        $this->recordAdminOperation(
            'login',
            ['username' => Auth::guard('admin')->user()->username],
            'POST',
            'api/auth/login',
            $ip,
            $userAgent
        );
    }

    /**
     * 记录退出日志
     *
     * @param string $ip
     * @param string $userAgent
     * @return void
     */
    protected function recordLogoutLog(string $ip, string $userAgent): void
    {
        $this->recordAdminOperation(
            'logout',
            ['username' => Auth::guard('admin')->user()->username],
            'POST',
            'api/auth/logout',
            $ip,
            $userAgent
        );
    }

    /**
     * 记录个人信息更新日志
     *
     * @param array $updatedFields
     * @param string $ip
     * @param string $userAgent
     * @return void
     */
    protected function recordProfileUpdateLog(array $updatedFields, string $ip, string $userAgent): void
    {
        $this->recordAdminOperation(
            'update_profile',
            ['updated_fields' => $updatedFields],
            'PUT',
            'api/auth/me',
            $ip,
            $userAgent
        );
    }
} 