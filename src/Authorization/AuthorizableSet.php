<?php

namespace Voice\JsonAuthorization\Authorization;

use Throwable;
use Voice\JsonAuthorization\Exceptions\AuthorizationException;

class AuthorizableSet
{
    public string $id;
    public string $type;
    public array $values;

    /**
     * AuthorizableSet constructor.
     * @param string $id
     * @param string $type
     * @param array $values
     * @throws Throwable
     */
    public function __construct(string $id, string $type, array $values)
    {
        $this->id = $id;
        $this->type = $type;
        $this->values = $values;

        $this->areValuesValid();
    }

    /**
     * @throws Throwable
     */
    protected function areValuesValid(): void
    {
        foreach ($this->values as $value) {
            throw_if(!is_string($value), new AuthorizationException("Authorizable sets must be a 2 dimensional array."));
        }
    }

    public function removeValue(string $value): void
    {
        $key = array_search($value, $this->values, true);

        if ($key !== false) {
            unset($this->values[$key]);
        }
    }

    public function removeValueByKey($key)
    {
        if (array_key_exists($key, $this->values)) {
            unset($this->values[$key]);
        }
    }
}
