<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\Database\Seeds;

use Asseco\JsonAuthorization\App\AuthorizableModel;
use Asseco\JsonAuthorization\App\AuthorizationRule;
use Illuminate\Database\Seeder;

class AuthorizationRuleSeeder extends Seeder
{
    public function run(): void
    {
        $role = config('asseco-authorization.universal_role');

        $authorizableModels = AuthorizableModel::all();

        foreach ($authorizableModels as $authorizableModel) {
            $rules = $this->generateRules();

            AuthorizationRule::query()->create([
                'authorizable_set_type_id' => 1,
                'authorizable_set_value'   => $role,
                'authorizable_model_id'    => $authorizableModel->id,
                'rules'                    => json_encode($rules, JSON_THROW_ON_ERROR),
            ]);
        }
    }

    protected function generateRules(): array
    {
        $rules = [];
        $rights = [
            'read', 'create', 'update', 'delete',
        ];

        $counter = rand(1, 4);

        for ($i = 0; $i < $counter; $i++) {
            $right = $rights[$i];

            if ($right === 'read') {
                $randRight = rand(1, 2);

                if ($randRight === 1) {
                    $rule = '*';
                } else {
                    $ids = $this->getRandomNumbers();

                    $rule = [
                        'search' => [
                            'id' => '=' . implode(';', $ids),
                        ],
                    ];
                }
            } else {
                $ids = $this->getRandomNumbers();

                $rule = [
                    'search' => [
                        'id' => '=' . implode(';', $ids),
                    ],
                ];
            }

            $rules[$rights[$i]] = $rule;
        }

        return $rules;
    }

    protected function getRandomNumbers(): array
    {
        $counter = rand(1, 4);

        $random = [];
        for ($i = 0; $i < $counter; $i++) {
            $random[$i] = rand(0, 50);
        }

        return $random;
    }
}
