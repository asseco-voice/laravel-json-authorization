<?php

namespace Voice\JsonAuthorization\App;

use Illuminate\Database\Eloquent\Model;
use Voice\JsonAuthorization\App\Traits\Cacheable;

class AuthorizableSetType extends Model
{
    use Cacheable;

    protected $guarded = ['id'];

    public function rules()
    {
        return $this->hasMany(AuthorizationRule::class, 'authorizable_set_type_id');
    }

    protected static function cacheKey(): string
    {
        return 'authorizable_set_types';
    }

    protected static function cacheAlternative(): array
    {
        return self::all(['id', 'name'])->toArray();
    }
}
