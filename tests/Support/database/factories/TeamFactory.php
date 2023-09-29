<?php

declare(strict_types=1);

namespace Rawilk\Settings\Tests\Support\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Rawilk\Settings\Tests\Support\Models\Team;

/**
 * @extends Factory<\Rawilk\Settings\Tests\Support\Models\Team>
 */
final class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
        ];
    }
}
