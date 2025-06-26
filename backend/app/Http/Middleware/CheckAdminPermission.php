<?php

namespace App\Http\Middleware;

use App\Services\AdminService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminPermission
{
    protected AdminService $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission = null): Response
    {
        $admin = auth('admin')->user();
        
        if (!$admin) {
            return response()->json([
                'code' => 401,
                'message' => '未登录或登录已过期'
            ], 401);
        }

        // 如果没有指定权限，则只检查是否登录
        if (!$permission) {
            return $next($request);
        }

        // 检查用户是否有指定权限
        if (!$this->adminService->hasPermission($admin, $permission)) {
            return response()->json([
                'code' => 403,
                'message' => '权限不足'
            ], 403);
        }

        return $next($request);
    }
}