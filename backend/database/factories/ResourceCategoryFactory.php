<?php

namespace Database\Factories;

use App\Models\ResourceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResourceCategoryFactory extends Factory
{
    protected $model = ResourceCategory::class;

    public function definition(): array
    {
        static $index = 1;
        
        return [
            'name' => '测试资源分类' . $index++,
            'sort' => $this->faker->numberBetween(0, 100),
        ];
    }
}