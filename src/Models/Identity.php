<?php

namespace PXP\Auth\Models;

use PXP\Data\Model;
use PXP\Ds\Obj;

abstract class Identity extends Model
{
    abstract public static function search(string $identifier): ?static;

    abstract public static function make(Obj $req): static;

    abstract public function identifier(): string;

    abstract public function secret(): ?string;

    abstract public function authenticate(Obj $req): bool;

    abstract public function name(): string;

    /**
     * @return array<string, string> column => label
     */
    abstract public static function nameColumns(): array;
}
