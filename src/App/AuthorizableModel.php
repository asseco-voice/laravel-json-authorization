<?php

namespace Voice\JsonAuthorization\App;

use Illuminate\Database\Eloquent\Model;

class AuthorizableModel extends Model
{
    protected $guarded = ['id'];

    public function rules()
    {
        return $this->hasMany(AuthorizationRule::class, 'authorization_rule_id');
    }
}
