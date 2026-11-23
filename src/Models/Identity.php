<?php

namespace PXP\Auth\Models;

use PXP\Data\Model;

abstract class Identity extends Model
{
    abstract public static function search(string $identifier): ?static;

    abstract public static function make(object $req): static;

    abstract public function identifier(): string;

    abstract public function secret(): ?string;

    abstract public function authenticate(object $req): bool;

    abstract public function name(): string;

    /**
     * @return array<string, string> column => label
     */
    abstract public static function nameColumns(): array;
}
