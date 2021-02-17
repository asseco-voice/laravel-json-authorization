<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\Database\Factories;

use Asseco\JsonAuthorization\App\Models\AuthorizableSetType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuthorizableSetTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AuthorizableSetType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'        => $this->faker->word,
            'description' => $this->faker->sentence,
            'created_at'  => now(),
            'updated_at'  => now(),
        ];
    }
}
