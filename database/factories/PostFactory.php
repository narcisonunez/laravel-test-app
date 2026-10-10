<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => rtrim(fake()->sentence(rand(3, 7)), '.'),
            'body' => implode("\n\n", fake()->paragraphs(2)),
            'author' => fake()->name(),
            'published_at' => fake()->dateTimeBetween('-3 months'),
        ];
    }
}
