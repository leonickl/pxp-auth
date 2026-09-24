<?php

namespace PXP\Auth;

readonly class Role
{
    private function __construct(public int $level) {}

    private static function roles(): array
    {
        return config('auth.roles.levels', [
            'REGULAR' => 0,
            'ADMIN' => 1,
        ]);
    }

    public static function DEFAULT(): self
    {
        return new self(0);
    }

    public static function __callStatic(string $name, array $args)
    {
        if (array_key_exists($name, self::roles())) {
            return new self(self::roles()[$name]);
        }

        throw new RuntimeException("Invalid static method call '$name'");
    }

    public static function try(?int $level): ?self
    {
        if (in_array($level, self::roles())) {
            return new self($level);
        }

        return null;
    }

    public static function valid(?int $level): bool
    {
        return self::try($level) !== null;
    }

    public static function make(int $level): self
    {
        return self::try($level) ?? self::DEFAULT();
    }

    public function name(): string
    {
        return array_search($this->role, self::roles());
    }

    public function equals(self $other): bool
    {
        return $this->role === $other->role;
    }

    public function atLeast(self $other): bool
    {
        return $this->level >= $other->level;
    }

    public function label(): string
    {
        return config('auth.roles.labels', [
            'REGULAR' => 'Regular',
            'ADMIN' => 'Admin',
        ])[$this->name()];
    }

    public function __toString()
    {
        return 'Role::'.$this->name();
    }
}
