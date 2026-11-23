<?php

namespace PXP\Auth\Models;

use PXP\Data\Model;

/**
 * @property int $id
 * @property string $user_id
 * @property string $selector
 * @property string $hashed_validator
 * @property string $expires
 */
class AuthToken extends Model
{
    protected string $table = 'auth_tokens';

    public static function forSelector(string $selector): ?self
    {
        return self::query()
            ->whereIs('selector', $selector)
            ->where('expires', '>=', date('Y-m-d H:i:s'))
            ->get()
            ->first();
    }

    public function isValid(string $validator): bool
    {
        return hash_equals($this->hashed_validator, hash('sha256', $validator));
    }
}
