<?php

namespace PXP\Auth\Models;

use Override;
use PXP\Auth\Lib\Mail;
use PXP\Auth\Role;
use PXP\Ds\Obj;
use PXP\Lib\Notification;

/**
 * @property int $id
 * @property string $email
 * @property string $password_hash
 * @property int $role
 * @property bool $verified
 */
class User extends Identity
{
    protected string $table = 'users';

    #[Override]
    public static function search(string $email): ?static
    {
        return static::findByOrNull('email', $email);
    }

    /**
     * @return array<string, string> column => label
     */
    #[Override]
    public static function nameColumns(): array
    {
        return config('auth.name-columns', ['name' => 'Name']);
    }

    protected static function nameAttributes(Obj $req): Obj
    {
        $attributes = [];

        foreach (static::nameColumns() as $column => $_) {
            $attributes[$column] = $req->{$column};
        }

        return o(...$attributes);
    }

    #[Override]
    public static function make(Obj $req): static
    {
        $user = static::create(
            ...static::nameAttributes($req),
            email: $req->identifier,
            password_hash: password_hash($req->secret, PASSWORD_DEFAULT),
        );

        $user->sendVerification();

        Notification::info("Benutzer '".$user->name()
            ."' ($user->email) wurde erstellt. Bitte E-Mail-Adresse verifizieren.");

        return $user;
    }

    #[Override]
    public function identifier(): string
    {
        return $this->email;
    }

    #[Override]
    public function secret(): string
    {
        return $this->password_hash;
    }

    #[Override]
    public function authenticate(Obj $req): bool
    {
        return password_verify($req->secret, $this->secret());
    }

    #[Override]
    public function name(): string
    {
        $parts = array_map(
            fn (string $column) => (string) $this->{$column},
            array_keys(static::nameColumns()),
        );

        return implode(' ', $parts);
    }

    public function setPasswordHash(string $password): void
    {
        $this->password_hash = password_hash($password, PASSWORD_DEFAULT);
    }

    public function role(): Role
    {
        return Role::make($this->role);
    }

    public function is(Role $role): bool
    {
        return $this->role()->equals($role);
    }

    public function sendVerification(): void
    {
        $link = VerificationLink::create(user_id: $this->id)->url();

        new Mail(
            subject: 'E-Mail-Adresse verifizieren',
            body: 'Klicke bitte auf den folgenden Link, um deine E-Mail-Adresse '.
                "zu verifizieren: <a href=\"$link\">$link</a>. Er ist 15 Minuten gültig.",
            html: true,
        )
            ->send($this->email, $this->name());
    }
}
