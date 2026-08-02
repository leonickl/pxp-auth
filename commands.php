<?php

use PXP\Auth\Models\Identity;
use PXP\Console\Command;

Command::new('create:user', function (string ...$args) {
    $identity = resolve(Identity::class);

    $columns = unstatic($identity)->nameColumns();
    $nameCount = count($columns);

    if (count($args) < $nameCount + 2) {
        $usage = implode(' ', [...array_keys($columns), 'email', 'password', '[role]']);
        exit("Usage: create:user $usage\n");
    }

    $user = unstatic($identity)->create(
        ...array_combine(array_keys($columns), array_slice($args, 0, $nameCount)),
        email: $args[$nameCount],
        password_hash: password_hash($args[$nameCount + 1], PASSWORD_DEFAULT),
        role: (int) ($args[$nameCount + 2] ?? '0'),
    );

    echo "created user with id $user->id\n";
});
