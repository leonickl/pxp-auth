<?php

namespace PXP\Auth\Controllers;

use PXP\Auth\Auth;
use PXP\Auth\Models\Identity;
use PXP\Auth\Models\Passkey;
use PXP\Auth\Passkeys;
use PXP\Auth\Validation\UserValidator;
use PXP\Http\Controllers\Controller;
use PXP\Http\Response\Redirect;
use PXP\Http\Response\Response;
use PXP\Lib\Notification;
use RuntimeException;

class LoginController extends Controller
{
    public function form(): Response
    {
        if (Auth::auth()) {
            return Redirect::route('main');
        }

        return view('login', [
            'passkeys' => config('auth.relying-party') !== null,
        ]);
    }

    public function login(): Response
    {
        if (request()->string('secret') === '') {
            Notification::warn('Bitte Passwort eingeben oder die Passkey-Anmeldung nutzen.');

            return Redirect::route('login');
        }

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

    public function validatePasskeyArgs()
    {
        return new Passkeys()->validateArgs();
    }

    public function validatePasskey()
    {
        $result = new Passkeys()->validate(
            id: request()->string('id'),
            client: request()->string('client'),
            auth: request()->string('auth'),
            sig: request()->string('sig'),
        );

        if ($result['status'] === 'ok') {
            $passkey = Passkey::findByCredentialId(request()->string('id'));
            $user = $passkey === null ? null : unstatic(resolve(Identity::class))->findOrNull($passkey->user_id);

            if ($user !== null) {
                session_regenerate_id();
                session(['identifier' => $user->identifier()]);
            }
        }

        return $result;
    }
}
