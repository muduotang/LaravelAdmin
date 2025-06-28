<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. 管理员表
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('username', 64)->unique()->comment('用户名');
            $table->string('password', 128)->comment('密码');
            $table->string('icon', 500)->nullable()->comment('头像');
            $table->string('email', 100)->unique()->comment('邮箱');
            $table->string('nick_name', 200)->nullable()->comment('昵称');
            $table->string('note', 500)->nullable()->comment('备注信息');
            $table->tinyInteger('status')->default(1)->comment('帐号启用状态：0->禁用；1->启用');
            $table->timestamps();
        });

        // 2. 角色表
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->comment('名称');
            $table->string('description', 500)->nullable()->comment('描述');
            $table->integer('admin_count')->default(0)->comment('用户数');
            $table->tinyInteger('status')->default(1)->comment('启用状态：0->禁用；1->启用');
            $table->integer('sort')->default(0)->comment('排序');
            $table->timestamps();
        });

        // 3. 用户-角色关联表
        Schema::create('admin_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['admin_id', 'role_id']);
        });

        // 4. 菜单表
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('menus')->onDelete('cascade');
            $table->string('title', 100)->comment('菜单名称');
            $table->integer('level')->default(0)->comment('菜单级数');
            $table->integer('sort')->default(0)->comment('排序');
            $table->string('name', 100)->nullable()->comment('前端路由名称');
            $table->string('icon', 200)->nullable()->comment('图标');
            $table->tinyInteger('hidden')->default(0)->comment('是否隐藏：0->显示；1->隐藏');
            $table->boolean('keep_alive')->default(true)->comment('是否缓存页面：1->缓存，0->不缓存');
            $table->timestamps();
        });

        // 5. 资源分类表
        Schema::create('resource_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200)->unique()->comment('分类名称');
            $table->integer('sort')->default(0)->comment('排序');
            $table->timestamps();
        });

        // 6. 资源表
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('resource_categories')->onDelete('cascade');
            $table->string('name', 200)->comment('资源名称');
            $table->string('route_name', 200)->unique()->comment('路由名称');
            $table->string('description', 500)->nullable()->comment('描述');
            $table->timestamps();
        });

        // 7. 角色-菜单关联表
        Schema::create('role_menu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['role_id', 'menu_id']);
        });

        // 8. 角色-资源关联表
        Schema::create('role_resource', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('resource_id')->constrained('resources')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['role_id', 'resource_id']);
        });

        // 9. 后台用户操作日志表
        Schema::create('admin_operation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->string('operation', 100)->comment('操作类型');
            $table->text('detail')->nullable()->comment('操作详情（JSON格式）');
            $table->string('ip', 64)->nullable()->comment('操作IP');
            $table->string('address', 100)->nullable()->comment('操作地址');
            $table->string('user_agent', 200)->nullable()->comment('浏览器/客户端信息');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_operation_logs');
        Schema::dropIfExists('role_resource');
        Schema::dropIfExists('role_menu');
        Schema::dropIfExists('resources');
        Schema::dropIfExists('resource_categories');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('admin_role');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('admins');
    }
};