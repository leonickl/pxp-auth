<?php

namespace PXP\Auth\Models;

use PXP\Data\Model;
use PXP\Ds\Vector;

/**
 * @property int $id
 * @property int $user_id
 * @property string $credential_id
 * @property string $public_key
 * @property int $sign_count
 */
class Passkey extends Model
{
    protected string $table = 'passkeys';

    protected function defaults(): array
    {
        return [
            'sign_count' => 0,
        ];
    }

    /**
     * @return Vector<static>
     */
    public static function forUser(Identity|int $user): Vector
    {
        $userId = $user instanceof Identity ? $user->id : $user;

        return static::findAllBy('user_id', $userId);
    }

    public static function findByCredentialId(string $credentialId): ?static
    {
        return static::findByOrNull('credential_id', $credentialId);
    }
}
