<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'description',
        'admin_count',
        'status',
        'sort',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    // 关联关系
    public function admins()
    {
        return $this->belongsToMany(Admin::class, 'admin_role');
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'role_menu');
    }

    public function resources()
    {
        return $this->belongsToMany(Resource::class, 'role_resource');
    }

    // 辅助方法
    public function hasPermission($routeName)
    {
        return $this->resources->contains(function ($resource) use ($routeName) {
            if ($resource->route_name === '*') {
                return true;
            }

            if (str_ends_with($resource->route_name, '.*')) {
                $prefix = substr($resource->route_name, 0, -2);
                return str_starts_with($routeName, $prefix . '.');
            }

            return $resource->route_name === $routeName;
        });
    }
} 