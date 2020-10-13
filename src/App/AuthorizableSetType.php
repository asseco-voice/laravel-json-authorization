<?php

declare(strict_types=1);

namespace Voice\JsonAuthorization\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Voice\JsonAuthorization\App\Traits\Cacheable;

class AuthorizableSetType extends Model
{
    use Cacheable;

    protected $fillable = ['name', 'description'];

    public function rules(): HasMany
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
