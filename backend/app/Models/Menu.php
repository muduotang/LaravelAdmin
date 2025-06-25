<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'parent_id',
        'title',
        'level',
        'sort',
        'name',
        'icon',
        'hidden',
        'keep_alive',
    ];

    protected $casts = [
        'hidden' => 'boolean',
        'keep_alive' => 'boolean',
    ];

    // 关联关系
    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_menu');
    }

    // 递归获取所有子菜单
    public function getAllChildren()
    {
        return $this->children()->with('getAllChildren');
    }

    // 递归获取所有父菜单
    public function getAllParents()
    {
        return $this->parent()->with('getAllParents');
    }
} 