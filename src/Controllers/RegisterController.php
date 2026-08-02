<?php

namespace PXP\Auth\Controllers;

use PXP\Auth\Models\Identity;
use PXP\Auth\Validation\UserValidator;
use PXP\Http\Controllers\Controller;
use PXP\Http\Response\Redirect;
use PXP\Http\Response\Response;
use PXP\Lib\Notification;

class RegisterController extends Controller
{
    public function form(): Response
    {
        return view('register');
    }

    public function register(): Response
    {
        $request = request()->validate(
            (new UserValidator)->validateRegister(...),
        );

        if (unstatic(resolve(Identity::class))->search($request->identifier) !== null) {
            Notification::warn('Benutzername/E-Mail-Adresse ist schon registriert.');

            return Redirect::route('register');
        }

        unstatic(resolve(Identity::class))->make($request);

        return Redirect::route('login');
    }
}
