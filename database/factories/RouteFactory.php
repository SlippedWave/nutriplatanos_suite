<?php

namespace Database\Factories;

use App\Models\Route;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Route>
 */
class RouteFactory extends Factory
{
    protected $model = Route::class;

    public function definition(): array
    {
        return [
            'carrier_id' => User::factory()->state(['role' => 'carrier']),
            'title' => 'Ruta ' . fake()->unique()->numberBetween(1, 100000),
            'status' => Route::STATUS_ACTIVE,
            'closed_at' => null,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn () => [
            'status' => Route::STATUS_CLOSED,
            'closed_at' => now(),
        ]);
    }
}
