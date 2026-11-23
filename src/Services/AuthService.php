<?php

namespace PXP\Auth\Services;

use PXP\Auth\Models\Identity;

class AuthService
{
    public function validate(object $request): bool
    {
        $identity = unstatic(resolve(Identity::class))
            ->search($request->identifier);

        if ($identity === null) {
            return false;
        }

        return $identity->authenticate($request);
    }

    public function login(string $identifier): bool
    {
        session_regenerate_id();
        session(['identifier' => $identifier]);

        return true;
    }

    public function logout(): void
    {
        session_destroy();
    }

    public function auth(): bool
    {
        return self::user() !== null;
    }

    public function user(): ?Identity
    {
        if (session('identifier') === null) {
            return null;
        }

        return unstatic(resolve(Identity::class))
            ->search(session('identifier'));
    }
}
