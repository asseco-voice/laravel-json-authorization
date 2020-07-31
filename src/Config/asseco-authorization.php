<?php

return [
    'asseco-authorization' => [
        /**
         * Path to Laravel models. This does not recurse in folders, so you need to specify
         * an array of paths if non-standard models are to be used
         */
        'models_path'      => [
            app_path()
        ],
        /**
         * Namespace for Laravel models.
         */
        'model_namespace'  => 'App\\',
        /**
         * Namespace to AuthorizesWithJson trait
         */
        'trait_path'       => 'Voice\JsonAuthorization\App\Traits\AuthorizesWithJson',

        /**
         * Who will have resolving precedence if OR resolution_type is used
         */
        'resolution_types' => [
            // RoleResolution::class
        ],
    ],
];
