<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\Database\Factories;

use Asseco\JsonAuthorization\App\Models\AuthorizableModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuthorizableModelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AuthorizableModel::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'       => $this->faker->word,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
