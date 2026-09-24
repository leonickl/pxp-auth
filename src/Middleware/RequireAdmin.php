<?php

namespace PXP\Auth\Middleware;

use PXP\Auth\Auth;
use PXP\Auth\Role;
use PXP\Exceptions\UnauthorizedException;
use PXP\Http\Middleware\Middleware;
use PXP\Http\Response\View;

class RequireAdmin extends Middleware
{
    public function apply(): true|View
    {
        if (! Auth::user()?->role()->atLeast(Role::ADMIN())) {
            throw new UnauthorizedException('Zugriff nur als Admin');
        }

        return true;
    }
}
