<?php

declare(strict_types=1);

namespace Rawilk\Settings\Tests\Support\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Rawilk\Settings\Tests\Support\Models\Company;

/**
 * @extends Factory<\Rawilk\Settings\Tests\Support\Models\Company>
 */
final class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
        ];
    }
}
