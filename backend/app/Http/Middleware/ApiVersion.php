<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ApiVersion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 从请求头获取版本
        $version = $request->header('Accept-Version');
        
        // 如果没有指定版本，使用默认版本
        if (! $version) {
            $version = config('api.versions.default');
        }

        // 检查版本是否支持
        if (! in_array($version, config('api.versions.supported'))) {
            throw new BadRequestHttpException('Unsupported API version.');
        }

        // 将版本信息添加到请求中
        $request->merge(['api_version' => $version]);

        return $next($request);
    }
} 