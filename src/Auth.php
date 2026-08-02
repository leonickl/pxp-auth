<?php

namespace PXP\Auth;

use PXP\Auth\Models\Identity;
use PXP\Ds\Obj;

/**
 * Facade for interactive authentication.
 * Cannot be used for basic auth.
 */
class Auth
{
    public static function login(Obj $request): bool
    {
        $identity = unstatic(resolve(Identity::class))->search($request->identifier);

        if ($identity === null) {
            return false;
        }

        if (! $identity->authenticate($request)) {
            return false;
        }

        session_regenerate_id();
        session(['identifier' => $request->identifier]);

        return true;
    }

    public static function logout(): void
    {
        session_destroy();
    }

    public static function auth(): bool
    {
        return session('identifier') !== null;
    }

    public static function user(): ?Identity
    {
        return self::auth()
            ? unstatic(resolve(Identity::class))->search(session('identifier'))
            : null;
    }
}
