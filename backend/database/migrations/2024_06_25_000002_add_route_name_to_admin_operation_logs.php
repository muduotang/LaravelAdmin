<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_operation_logs', function (Blueprint $table) {
            $table->string('method', 10)->default('GET')->after('operation')->comment('请求方法');
            $table->string('path', 200)->default('')->after('method')->comment('请求路径');
            $table->string('route_name', 200)->nullable()->after('path')->comment('路由名称');
            $table->renameColumn('detail', 'data');
        });
    }

    public function down(): void
    {
        Schema::table('admin_operation_logs', function (Blueprint $table) {
            $table->dropColumn(['method', 'path', 'route_name']);
            $table->renameColumn('data', 'detail');
        });
    }
}; 