<?php

namespace PXP\Auth;

use PXP\Auth\Models\Identity;
use PXP\Auth\Services\AuthService;
use PXP\Auth\Services\RememberSessionService;

/**
 * Facade for interactive authentication.
 * Cannot be used for basic auth.
 */
class Auth
{
    public static function login(object $request, bool $remember): bool
    {
        $service = new AuthService;

        if (! $service->validate($request)) {
            return false;
        }

        $service->login($request->identifier);

        if ($remember) {
            (new RememberSessionService)->rememberSession();
        }

        return true;
    }

    public static function logout(): void
    {
        (new AuthService)->logout();
        (new RememberSessionService)->destroySession();
    }

    public static function auth(): bool
    {
        return (new AuthService)->auth();
    }

    public static function user(): ?Identity
    {
        return (new AuthService)->user();
    }
}
