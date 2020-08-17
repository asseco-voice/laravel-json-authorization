<?php

namespace Voice\JsonAuthorization\App;

use Illuminate\Database\Eloquent\Model;

class AuthorizationRule extends Model
{
    protected $guarded = ['id'];

    public function model()
    {
        return $this->belongsTo(AuthorizableModel::class, 'authorization_model_id');
    }

    public function authorizableSetType()
    {
        return $this->belongsTo(AuthorizableSetType::class);
    }
}
