<?php

namespace PXP\Auth\Enums;

enum Role: int
{
    case REGULAR = 0;
    case ADMIN = 1;

    public static function make(int $role): self
    {
        return self::tryFrom($role) ?? self::REGULAR;
    }
}
