<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceCategory extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'sort',
    ];

    // 关联关系
    public function resources()
    {
        return $this->hasMany(Resource::class, 'category_id');
    }
}