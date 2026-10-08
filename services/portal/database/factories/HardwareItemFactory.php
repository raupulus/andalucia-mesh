<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\HardwareCategory;
use App\Models\HardwareItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory para generar artículos y placas de hardware de prueba.
 *
 * @extends Factory<HardwareItem>
 */
class HardwareItemFactory extends Factory
{
    protected $model = HardwareItem::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'category_id' => HardwareCategory::factory(),
            'slug' => Str::slug($name).'-'.fake()->unique()->randomNumber(4),
            'name' => [
                'es' => ucfirst($name),
                'en' => ucfirst($name),
            ],
            'description' => [
                'es' => fake()->paragraph(),
                'en' => fake()->paragraph(),
            ],
            'image_path' => 'hardware/test-item.jpg',
            'buy_url' => fake()->url(),
            'guide_url' => fake()->optional()->url(),
            'last_price' => fake()->randomFloat(2, 15, 120),
            'currency' => 'EUR',
            'is_featured' => fake()->boolean(20),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }
}
