<?php

namespace Database\Factories;

use App\Models\Resource;
use App\Models\ResourceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResourceFactory extends Factory
{
    protected $model = Resource::class;

    public function definition(): array
    {
        static $index = 1;
        
        return [
            'category_id' => ResourceCategory::factory(),
            'name' => '测试资源' . $index++,
            'route_name' => 'test.resource.' . $index,
            'description' => $this->faker->sentence(),
        ];
    }
}