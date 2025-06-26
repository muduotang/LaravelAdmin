<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;
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

    /**
     * 父级菜单
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /**
     * 子菜单
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id');
    }

    /**
     * 角色
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_menu')
            ->withTimestamps();
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