<?php

declare(strict_types=1);

namespace Voice\JsonAuthorization\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuthorizableModel extends Model
{
    protected $guarded = ['id'];

    public function rules(): HasMany
    {
        return $this->hasMany(AuthorizationRule::class, 'authorization_rule_id');
    }
}
