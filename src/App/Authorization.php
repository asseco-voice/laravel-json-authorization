<?php

namespace Voice\JsonAuthorization\App;

use Illuminate\Database\Eloquent\Model;

class Authorization extends Model
{
    protected $guarded = ['id'];

    public function model()
    {
        return $this->belongsTo(AuthorizationModel::class, 'authorization_model_id');
    }

    public function manageType()
    {
        return $this->belongsTo(AuthorizationManageType::class, 'authorization_manage_type_id');
    }
}
