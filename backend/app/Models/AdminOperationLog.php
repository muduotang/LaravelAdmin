<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminOperationLog extends Model
{
    protected $fillable = [
        'admin_id',
        'operation',
        'detail',
        'ip',
        'address',
        'user_agent',
    ];

    protected $casts = [
        'detail' => 'json',
    ];

    // 关联关系
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
} 