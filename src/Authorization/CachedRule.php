<?php

declare(strict_types=1);

namespace Voice\JsonAuthorization\Authorization;

class CachedRule
{
    public string $type;
    public string $value;
    public array $rules;
    private string $typeId;

    public function __construct(string $typeId, string $type, string $value, array $rules)
    {
        $this->typeId = $typeId;
        $this->type = $type;
        $this->value = $value;
        $this->rules = $rules;
    }

    public function prepare(): array
    {
        return [
            'typeId' => $this->typeId,
            'type'   => $this->type,
            'value'  => $this->value,
            'rules'  => $this->rules
        ];
    }
}
