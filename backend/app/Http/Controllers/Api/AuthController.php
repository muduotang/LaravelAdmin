<?php

namespace App\Http\Controllers\Api;

use App\Models\AdminOperationLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseController
{
    /**
     * 管理员登录
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        try {
            // 验证输入
            $validator = $this->validateLoginRequest($request);
            
            if ($validator->fails()) {
                return $this->error($validator->errors()->first(), 422, [
                    'errors' => $validator->errors(),
                ]);
            }

            $credentials = $validator->validated();
            
            // 尝试登录
            if (! $token = $this->attemptLogin($credentials)) {
                return $this->error('用户名或密码错误', 401);
            }

            // 记录登录日志
            $this->recordLoginLog($request);

            // 返回登录成功响应
            return $this->respondWithToken($token);
        } catch (\Exception $e) {
            Log::error('Login error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->error('登录失败：' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取当前登录用户信息
     *
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        try {
            $user = Auth::guard('admin')->user();

            return $this->success([
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'nick_name' => $user->nick_name,
                'icon' => $user->icon,
                'roles' => $user->roles()->get(['roles.id', 'roles.name', 'roles.description']),
            ], '获取个人信息成功');
        } catch (\Exception $e) {
            Log::error('Failed to get user info', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->error('获取用户信息失败：' . $e->getMessage());
        }
    }

    /**
     * 刷新令牌
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        try {
            return $this->respondWithToken(
                Auth::guard('admin')->refresh(),
                '令牌刷新成功'
            );
        } catch (\Exception $e) {
            Log::error('Token refresh failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->error('刷新令牌失败：' . $e->getMessage());
        }
    }

    /**
     * 退出登录
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // 记录退出日志
            $this->recordLogoutLog($request);
            
            // 执行退出操作
            Auth::guard('admin')->logout();
            
            return $this->success(null, '退出登录成功');
        } catch (\Exception $e) {
            Log::error('Logout failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->error('退出登录失败：' . $e->getMessage());
        }
    }

    /**
     * 修改认证用户数据
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        try {
            // 验证输入
            $validator = $this->validateUpdateRequest($request);
            
            if ($validator->fails()) {
                return $this->error($validator->errors()->first(), 422, [
                    'errors' => $validator->errors(),
                ]);
            }

            $user = Auth::guard('admin')->user();
            $data = $validator->validated();

            // 如果提供了新密码，则更新密码
            if (isset($data['old_password'])) {
                if (!Hash::check($data['old_password'], $user->password)) {
                    return $this->error('原密码错误', 422);
                }
                $data['password'] = Hash::make($data['new_password']);
                unset($data['old_password'], $data['new_password'], $data['new_password_confirmation']);
            }

            // 更新用户数据
            $user->update($data);

            // 记录操作日志
            AdminOperationLog::create([
                'admin_id' => $user->id,
                'operation' => 'update_profile',
                'detail' => [
                    'updated_fields' => array_keys($data),
                ],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $this->success([
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'nick_name' => $user->nick_name,
                'icon' => $user->icon,
            ], '个人信息更新成功');
        } catch (\Exception $e) {
            Log::error('Update profile failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->error('更新个人信息失败：' . $e->getMessage());
        }
    }

    /**
     * 验证登录请求
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validateLoginRequest(Request $request)
    {
        return validator($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * 尝试登录
     *
     * @param array $credentials
     * @return string|null
     */
    protected function attemptLogin(array $credentials): ?string
    {
        Log::info('Login attempt', ['credentials' => $credentials]);
        
        $token = Auth::guard('admin')->attempt($credentials);
        
        if ($token) {
            Log::info('Login successful', ['admin_id' => Auth::guard('admin')->id()]);
        } else {
            Log::info('Login failed - invalid credentials');
        }

        return $token;
    }

    /**
     * 返回令牌响应
     *
     * @param string $token
     * @param string|null $message
     * @return JsonResponse
     */
    protected function respondWithToken(string $token, ?string $message = '登录成功'): JsonResponse
    {
        $data = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('admin')->factory()->getTTL() * 60,
        ];

        return $this->success($data, $message);
    }

    /**
     * 记录登录日志
     *
     * @param Request $request
     * @return void
     */
    protected function recordLoginLog(Request $request): void
    {
        $admin = Auth::guard('admin')->user();
        
        AdminOperationLog::create([
            'admin_id' => $admin->id,
            'operation' => 'login',
            'detail' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * 记录退出日志
     *
     * @param Request $request
     * @return void
     */
    protected function recordLogoutLog(Request $request): void
    {
        $admin = Auth::guard('admin')->user();
        
        if ($admin) {
            AdminOperationLog::create([
                'admin_id' => $admin->id,
                'operation' => 'logout',
                'detail' => [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }
    }

    /**
     * 验证更新请求
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validateUpdateRequest(Request $request)
    {
        $rules = [
            'email' => 'sometimes|required|email|unique:admins,email,' . Auth::guard('admin')->id(),
            'nick_name' => 'sometimes|required|string|max:50',
            'icon' => 'sometimes|required|string|max:255',
            'old_password' => 'required_with:new_password',
            'new_password' => 'required_with:old_password|string|min:6|confirmed',
        ];

        return validator($request->all(), $rules, [
            'email.email' => '邮箱格式不正确',
            'email.unique' => '该邮箱已被使用',
            'nick_name.max' => '昵称不能超过50个字符',
            'icon.max' => '头像URL不能超过255个字符',
            'new_password.min' => '新密码不能少于6个字符',
            'new_password.confirmed' => '两次输入的密码不一致',
            'old_password.required_with' => '修改密码时必须提供原密码',
        ]);
    }
}
