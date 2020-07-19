<?php

namespace Voice\JsonAuthorization\App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Voice\JsonQueryBuilder\JsonQuery;

class AuthorizationScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $input = $this->getAuthValues();

        $jsonQuery = new JsonQuery($builder, $input);
        $jsonQuery->search();

        //$builder->where('age', '>', 200);
    }

    protected function getAuthValues(): array
    {
        // Dohvati iz baze, storaj u redis.

        return [];
    }
}
