<?php

namespace Voice\JsonAuthorization\App;

use Illuminate\Database\Eloquent\Model;

class AuthorizationManageType extends Model
{
    protected $guarded = ['id'];

    public function authorizations()
    {
        return $this->hasMany(Authorization::class);
    }
}
