<?php

namespace PXP\Auth\Middleware;

use PXP\Exceptions\UnauthorizedException;
use PXP\Http\Middleware\Middleware;

/**
 * Set up HTTP basic authentication with users stored
 * in config('credentials') as an associative array
 * mapping user names to their passwords.
 */
class BasicAuth extends Middleware
{
    public function apply(): mixed
    {
        $credentials = (array) config('credentials', []);

        if (! isset($_SERVER['PHP_AUTH_USER'])) {
            self::unauthorized();
        }

        if (! array_key_exists($_SERVER['PHP_AUTH_USER'], $credentials)) {
            self::unauthorized();
        }

        $password = $credentials[$_SERVER['PHP_AUTH_USER']];

        if ($password !== $_SERVER['PHP_AUTH_PW']) {
            self::unauthorized();
        }

        return true;
    }

    private static function unauthorized(): never
    {
        header('WWW-Authenticate: Basic realm="My Protected Area"');
        header('HTTP/1.0 401 Unauthorized');

        throw new UnauthorizedException;
    }
}
