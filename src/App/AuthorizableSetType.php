<?php

namespace Voice\JsonAuthorization\App;

use Illuminate\Database\Eloquent\Model;

class AuthorizableSetType extends Model
{
    protected $guarded = ['id'];

    public function rules()
    {
        return $this->hasMany(AuthorizationRule::class, 'authorization_rule_id');
    }
}
