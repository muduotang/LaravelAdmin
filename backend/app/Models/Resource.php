<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'route_name',
        'description',
    ];

    // 关联关系
    public function category()
    {
        return $this->belongsTo(ResourceCategory::class, 'category_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_resource');
    }

    // 辅助方法
    public function matchRoute($routeName)
    {
        if ($this->route_name === '*') {
            return true;
        }

        if (str_ends_with($this->route_name, '.*')) {
            $prefix = substr($this->route_name, 0, -2);
            return str_starts_with($routeName, $prefix . '.');
        }

        return $this->route_name === $routeName;
    }
} 