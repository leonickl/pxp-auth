<?php

namespace PXP\Auth\Controllers;

use PXP\Auth\Auth;
use PXP\Auth\Validation\UserValidator;
use PXP\Http\Controllers\Controller;
use PXP\Http\Response\Redirect;
use PXP\Http\Response\Response;
use PXP\Lib\Notification;

class LoginController extends Controller
{
    public function form(): Response
    {
        if (Auth::auth()) {
            return Redirect::route('main');
        }

        return view('login');
    }

    public function login(): Response
    {
        $request = request()->validate(
            (new UserValidator)->validateLogin(...),
        );

        if (! Auth::login($request)) {
            Notification::warn('Ungültige Zugangsdaten gegeben.');

            return Redirect::route('login');
        }

        if (! Auth::user()) {
            return Redirect::route('login');
        }

        return Redirect::route('main');
    }

    public function logout(): Response
    {
        Auth::logout();

        return Redirect::route('main');
    }
}
