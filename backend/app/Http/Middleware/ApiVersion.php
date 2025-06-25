<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\ApiResponse;

class ApiVersion
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $version): Response
    {
        // 从请求头获取版本信息
        $requestVersion = $request->header('Accept')
            ? preg_match('/version=(\d+)/', $request->header('Accept'), $matches)
                ? $matches[1]
                : null
            : null;

        // 如果没有在 Accept 头中找到版本，尝试从自定义头中获取
        if (!$requestVersion) {
            $requestVersion = $request->header('X-API-Version');
        }

        // 如果没有在请求头中找到版本信息，使用 URL 中的版本
        if (!$requestVersion) {
            $requestVersion = $version;
        }

        // 版本不匹配时返回错误
        if ($requestVersion != $version) {
            return $this->error(
                "API version mismatch. Requested version: {$requestVersion}, Required version: {$version}",
                Response::HTTP_BAD_REQUEST
            );
        }

        return $next($request);
    }
} 