<?php

namespace PXP\Auth;

use PXP\Auth\Models\Identity;
use PXP\Auth\Services\AuthService;

/**
 * Facade for interactive authentication.
 * Cannot be used for basic auth.
 */
class Auth
{
    public static function login(object $request): bool
    {
        $service = new AuthService;

        if ($service->validate($request)) {
            $service->login($request->identifier);

            return true;
        }

        return false;
    }

    public static function logout(): void
    {
        (new AuthService)->logout();
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
