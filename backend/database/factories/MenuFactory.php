<?php

namespace Database\Factories;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition(): array
    {
        static $sort = 0;
        
        return [
            'parent_id' => null,
            'title' => $this->faker->words(2, true),
            'level' => 0,
            'sort' => $sort++,
            'name' => $this->faker->unique()->word(),
            'icon' => 'el-icon-' . $this->faker->word(),
            'hidden' => false,
            'keep_alive' => true,
        ];
    }

    /**
     * 设置为子菜单
     *
     * @param int $parentId
     * @param int $level
     * @return Factory
     */
    public function child(int $parentId, int $level = 1): Factory
    {
        return $this->state(function (array $attributes) use ($parentId, $level) {
            return [
                'parent_id' => $parentId,
                'level' => $level,
            ];
        });
    }

    /**
     * 设置为隐藏菜单
     *
     * @return Factory
     */
    public function hidden(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'hidden' => true,
            ];
        });
    }
}