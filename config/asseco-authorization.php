<?php

use Asseco\BlueprintAudit\App\MigrationMethodPicker;
use Asseco\JsonAuthorization\App\Models\AuthorizableModel;
use Asseco\JsonAuthorization\App\Models\AuthorizableSetType;
use Asseco\JsonAuthorization\App\Models\AuthorizationRule;
use Asseco\JsonAuthorization\App\Traits\Authorizable;

return [

    /**
     * Model bindings.
     */
    'models' => [
        'authorizable_model'    => AuthorizableModel::class,
        'authorizable_set_type' => AuthorizableSetType::class,
        'authorization_rule'    => AuthorizationRule::class,
    ],

    'migrations'             => [

        /**
         * UUIDs as primary keys.
         */
        'uuid'       => false,

        /**
         * Timestamp types.
         *
         * @see https://github.com/asseco-voice/laravel-common/blob/master/config/asseco-common.php
         */
        'timestamps' => MigrationMethodPicker::PLAIN,

        /**
         * Should the package run the migrations. Set to false if you're publishing
         * and changing default migrations.
         */
        'run'        => true,
    ],

    /**
     * Path to Laravel models in 'path => namespace' format.
     *
     * This does not recurse in folders, so you need to specify
     * an array of paths if non-standard models are to be used
     */
    'models_path'            => [
        app_path('Models') => 'App\\Models\\',
    ],

    /**
     * Namespace to Authorizable trait.
     */
    'trait_path'             => Authorizable::class,

    /**
     * List of roles/groups/etc which have absolute admin/root rights.
     * Key must resemble names from authorization_manage_types table.
     */
    'absolute_rights'        => [
        // 'roles' => [
        //      'asseco-voice-admin'
        // ],
        // 'groups' => [
        //     'asseco-voice-admin'
        // ],
    ],

    /**
     * For dev purposes. Setting to true will ignore authorization completely.
     */
    'override_authorization' => env('OVERRIDE_AUTHORIZATION', false) === true,

    /**
     * Virtual role whose rules will attach to any authenticated user. DO NOT add this role to
     * your auth service (or whoever is responsible for providing user roles). Check readme
     * for more details.
     */
    'virtual_role'           => env('VIRTUAL_ROLE', 'voice-all-mighty'),
];
