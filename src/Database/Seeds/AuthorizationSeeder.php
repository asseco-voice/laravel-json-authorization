<?php

namespace Voice\JsonAuthorization\Database\Seeds;

use Illuminate\Database\Seeder;

class AuthorizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rule1 = [
            'read'   => '*',
            'create' => '*',
            'update' => [
                'search' => [
                    'id' => '<5',
                ],
            ],
        ];

        $rule2 = [
            'read' => [
                'search' => [
                    'id' => '=3;4;6;7',
                ],
            ],
        ];

        $rule3 = [
            'read'   => [
                'search' => [
                    'id' => '>15',
                ],
            ],
            'delete' => '*',
        ];

        $rule4 = [
            'read' => '*',
        ];

        $rule5 = [
            'delete' => '*',
        ];

        $rules = [
            json_encode($rule1),
            json_encode($rule2),
            json_encode($rule3),
            json_encode($rule4),
            json_encode($rule5),
        ];
    }
}
