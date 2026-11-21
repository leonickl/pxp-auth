<?php

use PXP\Data\DB;
use PXP\Data\Migrations\Migration;

return new Migration(function (DB $db) {
    $db->create('passkeys', [
        'user_id' => 'int not null references users(id)',
        'credential_id' => 'text not null',
        'public_key' => 'text not null',
        'sign_count' => 'int not null default 0',
    ]);

    $db->sql('create unique index if not exists '.
        'unique_passkeys_credential_id on passkeys(credential_id)');
});
