<?php

namespace Voice\JsonAuthorization\App;

use Illuminate\Database\Eloquent\Model;

class AuthorizationModel extends Model
{
    protected $guarded = ['id'];

    public function authorizations()
    {
        return $this->hasMany(Authorization::class);
    }
}
