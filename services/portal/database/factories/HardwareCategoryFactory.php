<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\HardwareCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory para generar categorías de hardware de prueba.
 *
 * @extends Factory<HardwareCategory>
 */
class HardwareCategoryFactory extends Factory
{
    protected $model = HardwareCategory::class;

    public function definition(): array
    {
        $nameEs = fake()->words(2, true);
        $nameEn = fake()->words(2, true);

        return [
            'slug' => Str::slug($nameEs).'-'.fake()->unique()->randomNumber(4),
            'name' => [
                'es' => ucfirst($nameEs),
                'en' => ucfirst($nameEn),
            ],
            'description' => [
                'es' => fake()->sentence(),
                'en' => fake()->sentence(),
            ],
            'sort_order' => fake()->numberBetween(0, 50),
            'is_active' => true,
        ];
    }
}
