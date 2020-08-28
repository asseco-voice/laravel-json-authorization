<?php

namespace Voice\JsonAuthorization\Authorization;

use Illuminate\Database\Eloquent\Collection;
use Throwable;
use Voice\JsonAuthorization\Exceptions\AuthorizationException;

class CachedRuleCollection extends Collection
{
    /**
     * @param mixed $item
     * @return CachedRuleCollection
     * @throws Throwable
     */
    public function add($item)
    {
        throw_if(!($item instanceof CachedRule), new AuthorizationException("Wrong item added to the collection."));
        return parent::add($item);
    }
}
