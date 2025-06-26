<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use App\Traits\ApiResponse;
use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Arr;

class Handler extends ExceptionHandler
{
    use ApiResponse;

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            return $this->error('未经授权', 401);
        });

        // 处理业务异常
        $this->renderable(function (BusinessException $e) {
            return response()->json([
                'status' => 'error',
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'data' => null,
            ], $e->getCode());
        });

        // 处理模型未找到异常
        $this->renderable(function (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => '请求的资源不存在',
                'data' => null,
            ], 404);
        });

        // 处理路由未找到异常
        $this->renderable(function (NotFoundHttpException $e) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => '请求的接口不存在',
                'data' => null,
            ], 404);
        });
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @param \Illuminate\Http\Request $request
     * @param ValidationException $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'status' => 'error',
            'code' => $exception->status,
            'message' => $exception->validator->errors()->first(),
            'data' => null,
            'errors' => $exception->errors(),
        ], $exception->status);
    }

    /**
     * Convert the given exception to an array.
     *
     * @param  \Throwable  $e
     * @return array
     */
    protected function convertExceptionToArray(Throwable $e)
    {
        return config('app.debug') ? [
            'status' => 'error',
            'code' => 500,
            'message' => $e->getMessage(),
            'data' => null,
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => collect($e->getTrace())->map(fn ($trace) => Arr::except($trace, ['args']))->all(),
        ] : [
            'status' => 'error',
            'code' => 500,
            'message' => $this->isHttpException($e) ? $e->getMessage() : '服务器内部错误',
            'data' => null,
        ];
    }
}
