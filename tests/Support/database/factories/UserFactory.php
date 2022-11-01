<?php

declare(strict_types=1);

namespace Rawilk\Settings\Tests\Support\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Rawilk\Settings\Tests\Support\Models\User;

/**
 * @extends Factory<\Rawilk\Settings\Tests\Support\Models\User>
 */
final class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
        ];
    }
}
