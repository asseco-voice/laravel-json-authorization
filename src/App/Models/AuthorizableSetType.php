<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\App\Models;

use Asseco\JsonAuthorization\App\Traits\Cacheable;
use Asseco\JsonAuthorization\Database\Factories\AuthorizableSetTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuthorizableSetType extends Model
{
    use Cacheable, HasFactory;

    protected $fillable = ['name', 'description'];

    protected static function newFactory()
    {
        return AuthorizableSetTypeFactory::new();
    }

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
