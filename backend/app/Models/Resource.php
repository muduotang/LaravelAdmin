<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Resource extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'route_name',
        'description',
    ];

    /**
     * 资源分类
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ResourceCategory::class, 'category_id');
    }

    /**
     * 角色
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_resource')
            ->withTimestamps();
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