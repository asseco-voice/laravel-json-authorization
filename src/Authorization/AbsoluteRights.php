<?php

declare(strict_types=1);

namespace Voice\JsonAuthorization\Authorization;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class AbsoluteRights
{
    protected array $absoluteRights;

    public function __construct()
    {
        $this->absoluteRights = config('asseco-authorization.absolute_rights');
    }

    public function check(Collection $authorizationRules): bool
    {
        foreach ($this->absoluteRights as $absoluteRightType => $absoluteRightValues) {
            $userRules = $authorizationRules->where('type', $absoluteRightType);

            if ($userRules->isEmpty()) {
                continue;
            }

            $absoluteRightValues = Arr::wrap($absoluteRightValues);

            foreach ($absoluteRightValues as $absoluteRightValue) {
                if ($userRules->pluck('value')->contains($absoluteRightValue)) {
                    return true;
                }
            }
        }

        return false;
    }
}
