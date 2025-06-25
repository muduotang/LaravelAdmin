<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ExampleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API 版本路由组
Route::prefix('v1')->group(function () {
    // API响应示例路由
    Route::prefix('example')->group(function () {
        Route::get('success', [ExampleController::class, 'successExample']);
        Route::get('error', [ExampleController::class, 'errorExample']);
        Route::get('paginate', [ExampleController::class, 'paginateExample']);
        Route::get('collection', [ExampleController::class, 'collectionExample']);
    });
});

// 默认重定向到最新版本
Route::get('/', function () {
    return redirect('/api/' . config('api.versions.newest'));
});
