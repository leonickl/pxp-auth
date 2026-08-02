<?php

namespace PXP\Auth\Validation;

use PXP\Auth\Models\Identity;
use PXP\Data\Validate\ValidationProxy;
use PXP\Data\Validate\Validator;

class UserValidator
{
    /**
     * @return list<Validator>
     */
    public function validateLogin(ValidationProxy $req): array
    {
        return [
            $req->identifier->string(),
            $req->secret->string(),
        ];
    }

    /**
     * @return list<Validator>
     */
    public function validateRegister(ValidationProxy $req): array
    {
        $nameRules = array_map(
            fn (string $column) => $req->{$column}->string()->min(3)->max(40),
            array_keys(unstatic(resolve(Identity::class))->nameColumns()),
        );

        return [
            $req->identifier->string()->email()->min(6)->max(50),
            ...$nameRules,
            $req->secret->string()->min(8)->max(100),
        ];
    }
}
