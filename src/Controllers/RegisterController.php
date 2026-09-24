<?php

namespace PXP\Auth\Controllers;

use PXP\Auth\Models\Identity;
use PXP\Auth\Passkeys;
use PXP\Auth\Validation\UserValidator;
use PXP\Http\Controllers\Controller;
use PXP\Http\Response\Redirect;
use PXP\Http\Response\Response;
use PXP\Lib\Notification;
use RuntimeException;

class RegisterController extends Controller
{
    public function form(): Response
    {
        return view('register', [
            'nameColumns' => unstatic(resolve(Identity::class))->nameColumns(),
            'passkeys' => config('auth.relying-party') !== null,
        ]);
    }

    public function passkeyForm(): Response
    {
        return view('register-passkey', [
            'nameColumns' => unstatic(resolve(Identity::class))->nameColumns(),
        ]);
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

    public function registerPasskeyArgs()
    {
        $identifier = request()->string('identifier');

        $user = unstatic(resolve(Identity::class))->search($identifier);

        if ($user === null) {
            $attributes = o(
                identifier: $identifier,
                secret: bin2hex(random_bytes(32)),
            )->with(...$this->names());

            $user = unstatic(resolve(Identity::class))->make($attributes);
        }

        return new Passkeys()->registerArgs($user);
    }

    public function registerPasskey(): mixed
    {
        return new Passkeys()->register(
            client: request()->string('client'),
            attest: request()->string('attest'),
        );
    }

    private function names(): array
    {
        $names = [];

        foreach (array_keys(unstatic(resolve(Identity::class))->nameColumns()) as $column) {
            $names[$column] = request()->string($column);
        }

        return $names;
    }
}
