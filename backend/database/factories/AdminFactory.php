<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Admin>
 */
class AdminFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Admin::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $index = 1;
        
        return [
            'username' => 'admin' . $index++,
            'password' => Hash::make('password'),
            'email' => $this->faker->unique()->safeEmail(),
            'nick_name' => $this->faker->name(),
            'note' => $this->faker->sentence(),
            'status' => 1,
        ];
    }
} 