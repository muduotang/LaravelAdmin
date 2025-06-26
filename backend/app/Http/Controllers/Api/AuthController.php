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
        $token = $this->authService->login($request->validated());
        return $this->success(['token' => $token]);
    }

    /**
     * 管理员退出
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        $this->authService->logout();
        return $this->success();
    }

    /**
     * 获取当前管理员信息
     *
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        $admin = $this->authService->getCurrentAdmin();
        return $this->success($admin);
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
     * 修改认证用户数据
     *
     * @param UpdateProfileRequest $request
     * @return JsonResponse
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $admin = $this->authService->updateProfile($request->validated());
        return $this->success($admin);
    }
}
