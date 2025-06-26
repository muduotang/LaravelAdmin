<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends BaseController
{
    /**
     * @var AuthService
     */
    protected AuthService $authService;

    /**
     * AuthController constructor.
     *
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * 管理员登录
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $this->authService->login(
            $request->validated(),
            $request->ip(),
            $request->userAgent()
        );

        return $this->success($data, '登录成功');
    }

    /**
     * 获取当前登录用户信息
     *
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        $data = $this->authService->getCurrentUser();
        return $this->success($data, '获取个人信息成功');
    }

    /**
     * 刷新令牌
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        $data = $this->authService->refreshToken();
        return $this->success($data, '令牌刷新成功');
    }

    /**
     * 退出登录
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->ip(), $request->userAgent());
        return $this->success(null, '退出登录成功');
    }

    /**
     * 修改认证用户数据
     *
     * @param UpdateProfileRequest $request
     * @return JsonResponse
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $data = $this->authService->updateProfile(
            $request->validated(),
            $request->ip(),
            $request->userAgent()
        );

        return $this->success($data, '个人信息更新成功');
    }
}
