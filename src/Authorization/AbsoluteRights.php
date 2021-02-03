<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\Authorization;

use Asseco\JsonAuthorization\App\Models\AuthorizableSetType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class AbsoluteRights
{
    public static function hasRole(Collection $authorizationRules): bool
    {
        $absoluteRights = config('asseco-authorization.absolute_rights');

        foreach ($absoluteRights as $authorizableSetType => $authorizableSetValues) {
            $resolvedSetType = AuthorizableSetType::cached()->where('name', $authorizableSetType)->first();
            $setTypeId = Arr::get($resolvedSetType, 'id');

            $userRules = $authorizationRules->where('authorizable_set_type_id', $setTypeId);

            if ($userRules->isEmpty()) {
                continue;
            }

            foreach (Arr::wrap($authorizableSetValues) as $authorizableSetValue) {
                if ($userRules->pluck('authorizable_set_value')->contains($authorizableSetValue)) {
                    return true;
                }
            }
        }

        return false;
    }
}
