<?php

namespace PXP\Auth\Middleware;

use PXP\Auth\Auth;
use PXP\Auth\Models\AuthToken;
use PXP\Auth\Services\RememberSessionService;
use PXP\Http\Middleware\Middleware;

/**
 * Checks whether a valid remember token exists
 * and if yes, logs the user back in.
 */
class RememberSession extends Middleware
{
    public function apply(): mixed
    {
        if (Auth::auth()) {
            return true;
        }

        $service = new RememberSessionService;

        $cookie = $service->readCookie();
        $token = AuthToken::forSelector($cookie?->selector);

        if ($token?->isValid($cookie->validator)) {
            $service->login($token);

            return true;
        }

        $service->destroySession();

        return true;
    }
}
