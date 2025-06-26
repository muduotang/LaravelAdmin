<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Admin extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = [
        'username',
        'password',
        'icon',
        'email',
        'nick_name',
        'note',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    // JWT 相关方法
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // 关联关系
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'admin_role');
    }

    public function operationLogs()
    {
        return $this->hasMany(AdminOperationLog::class);
    }

    // 辅助方法
    public function hasRole($role)
    {
        return $this->roles->contains('name', $role);
    }

    public function hasAnyRole($roles)
    {
        return $this->roles->whereIn('name', (array) $roles)->isNotEmpty();
    }

    public function hasAllRoles($roles)
    {
        $roles = (array) $roles;
        return $this->roles->whereIn('name', $roles)->count() === count($roles);
    }
} 