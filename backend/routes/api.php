<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\ResourceCategoryController;
use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\RoleController;

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

// 认证相关路由
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');

    Route::middleware('auth:admin')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::post('refresh', [AuthController::class, 'refresh'])->name('auth.refresh');
        Route::get('me', [AuthController::class, 'me'])->name('auth.me');
        Route::put('me', [AuthController::class, 'updateProfile'])->name('auth.updateProfile');
    });
});

// 需要认证的路由
Route::middleware('auth:admin')->group(function () {
    // 用户管理路由
    Route::apiResource('admins', AdminController::class);
    Route::post('admins/{admin}/roles', [AdminController::class, 'assignRoles'])->name('admins.assignRoles');
    Route::post('admins/{admin}/reset-password', [AdminController::class, 'resetPassword'])->name('admins.resetPassword');
    Route::post('admins/{admin}/status', [AdminController::class, 'updateStatus'])->name('admins.updateStatus');
    
    // 角色管理路由
    Route::apiResource('roles', RoleController::class);
    Route::post('roles/{role}/resources', [RoleController::class, 'assignResources'])->name('roles.assignResources');
    Route::post('roles/{role}/menus', [RoleController::class, 'assignMenus'])->name('roles.assignMenus');
    Route::get('roles/{role}/menus', [RoleController::class, 'getMenus'])->name('roles.getMenus');
    Route::get('roles/{role}/resources', [RoleController::class, 'getResources'])->name('roles.getResources');
    
    // 菜单管理
    Route::get('menus/tree', [MenuController::class, 'tree'])->name('menus.tree');
    Route::apiResource('menus', MenuController::class);
    
    // 资源分类管理
    Route::apiResource('resource-categories', ResourceCategoryController::class);
    
    // 资源管理
    Route::apiResource('resources', ResourceController::class);
});

// 默认重定向到最新版本
Route::get('/', function () {
    return redirect('/api/' . config('api.versions.newest'));
});
