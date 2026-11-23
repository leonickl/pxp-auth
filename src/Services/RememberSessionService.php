<?php

namespace PXP\Auth\Services;

use PXP\Auth\Auth;
use PXP\Auth\Models\User;
use RuntimeException;
use PXP\Auth\Models\AuthToken;

class RememberSessionService
{
    public function readCookie(): ?object
    {
        if (! isset($_COOKIE['remember_me'])) {
            return null;
        }

        $cookie = explode(':', $_COOKIE['remember_me'], 2);

        return obj(
            selector: $cookie[0],
            validator: $cookie[1],
        );
    }

    /**
     * Creates an auth token for the current user
     * and sets the remember-me-cookie.
     */
    public function rememberSession(): void
    {
        $selector = bin2hex(random_bytes(9));
        $validator = bin2hex(random_bytes(33));

        $expires = time() + 60 * 60 * 24 * 30;  // 30 days

        $user = Auth::user();

        if (! $user) {
            throw new RuntimeException('Need logged-in user to remember their session.');
        }

        AuthToken::create(
            user_identifier: $user->identifier(),
            selector: $selector,
            hashed_validator: hash('sha256', $validator),
            expires: date('Y-m-d H:i:s', $expires),
        );

        setcookie(
            name: 'remember_me',
            value: $selector.':'.$validator,
            expires_or_options: $expires,
            path: '/',
            domain: '',
            secure: true,
            httponly: true,
        );
    }

    /**
     * Removed the remember-me-cooke again.
     */
    public function destroySession(): void
    {
        setcookie('remember_me', '', time() - 3600, '/');
    }

    public function login(AuthToken $token): void
    {
        (new AuthService)->login($token->user_identifier);

        $this->rotateSession($token);
    }

    public function rotateSession(AuthToken $token): void
    {
        $token->delete();
        $this->rememberSession();
    }
}
