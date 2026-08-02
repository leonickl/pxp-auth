<?php

namespace PXP\Auth\Controllers;

use PXP\Auth\Models\Identity;
use PXP\Auth\Models\VerificationLink;
use PXP\Exceptions\DisplayException;
use PXP\Http\Controllers\Controller;
use PXP\Http\Response\Redirect;
use PXP\Http\Response\Response;
use PXP\Lib\Notification;

class VerificationController extends Controller
{
    public function verify(): Response
    {
        $token = request()->string('token');

        $link = VerificationLink::findByOrNull('token', $token) ??
            error(DisplayException::class, 'Ungültiger Token');

        $link->isValid() ?:
            error(DisplayException::class, 'Token abgelaufen');

        unstatic(resolve(Identity::class))->find($link->user_id)->fill(verified: true)->save();

        Notification::success('E-Mail-Adresse erfolgreich verifiziert. Du kannst dich jetzt einloggen.');

        return Redirect::route('login');
    }
}
