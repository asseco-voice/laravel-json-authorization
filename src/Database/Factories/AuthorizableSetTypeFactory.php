<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AuthorizableSetTypeFactory extends Factory
{
    public function modelName()
    {
        return config('asseco-authorization.models.authorizable_set_type');
    }

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
