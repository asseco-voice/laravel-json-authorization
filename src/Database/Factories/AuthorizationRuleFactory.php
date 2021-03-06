<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\Database\Factories;

use Asseco\JsonAuthorization\App\Models\AuthorizationRule;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuthorizationRuleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AuthorizationRule::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'authorizable_set_type_id' => $this->faker->randomNumber(),
            'authorizable_set_value'   => $this->faker->word,
            'authorizable_model_id'    => $this->faker->randomNumber(),
            'rules'                    => json_encode($this->faker->words(10)),
            'created_at'               => now(),
            'updated_at'               => now(),
        ];
    }
}
