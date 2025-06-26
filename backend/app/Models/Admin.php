<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Admin extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

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
        'status' => 'integer',
    ];

    /**
     * 角色
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'admin_role')
            ->withTimestamps();
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
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