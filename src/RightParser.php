<?php

namespace Voice\JsonAuthorization;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Voice\JsonAuthorization\App\Authorization;
use Voice\JsonAuthorization\App\AuthorizationModel;

class RightParser
{
    const CREATE_RIGHT = 'create';
    const READ_RIGHT = 'read';
    const UPDATE_RIGHT = 'update';
    const DELETE_RIGHT = 'delete';

    public static array $eventsToListen = [
        // 'eloquent.retrieved*', // Covered with scopes
        'eloquent.creating*',
        'eloquent.updating*',
        'eloquent.deleting*',
    ];

    public static array $eventRightMapping = [
        'eloquent.creating' => self::CREATE_RIGHT,
        'eloquent.updating' => self::UPDATE_RIGHT,
        'eloquent.deleting' => self::DELETE_RIGHT,
    ];

    public static function getAuthValues(string $model, string $right = self::READ_RIGHT): array
    {
        // a odakle će doći token?
        $role = 'agent';

        // provjeri ako postoji u cache...ako ne postoji, vidi jel postoji u bazi, ako ni tamo, insertaj
        // nakon svega pobacaj u cache
        $modelId = optional(AuthorizationModel::where('name', $model)->first())->id;

        if (!$modelId) {
            return [];
        }

        // ako nema isto zabrani sve
        $rules = optional(Authorization::where([
            'role'                   => $role,
            'authorization_model_id' => $modelId,
        ])->first())->rules;

        if (!$rules) {
            return [];
        }

        $rules = json_decode($rules, true);

        if (!array_key_exists($right, $rules)) {
            return [];
        }

        $wrapped = Arr::wrap($rules[$right]);

        Log::info("Found rules for '$right' right: " . print_r($wrapped, true));

        return $wrapped;

    }
}
