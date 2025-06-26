<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        static $index = 1;
        
        return [
            'name' => '测试角色' . $index++,
            'description' => $this->faker->sentence(),
        ];
    }
} 