<?php

use PXP\Auth\Auth;

function auth(): bool
{
    return Auth::auth();
}
