<?php

namespace PXP\Auth\Middleware;

use PXP\Http\Middleware\Middleware;
use PXP\Http\Response\View;

class InteractiveAuth extends Middleware
{
    public function apply(): true|View
    {
        return auth() ? true : view('login', [
            'errors' => session()->take('errors', []),
        ]);
    }
}
