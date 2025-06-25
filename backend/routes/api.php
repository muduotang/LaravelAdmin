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

// API响应示例路由
Route::prefix('example')->group(function () {
    Route::get('success', [ExampleController::class, 'successExample']);
    Route::get('error', [ExampleController::class, 'errorExample']);
    Route::get('paginate', [ExampleController::class, 'paginateExample']);
    Route::get('collection', [ExampleController::class, 'collectionExample']);
});
