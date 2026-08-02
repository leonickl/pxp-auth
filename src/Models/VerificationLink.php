<?php

namespace PXP\Auth\Models;

use Carbon\Carbon;
use Override;
use PXP\Data\Model;

/**
 * @property string $token
 * @property int $user_id
 * @property string $created_at
 */
class VerificationLink extends Model
{
    protected string $table = 'verification_link';

    #[Override]
    protected function defaults(): array
    {
        return [
            'token' => uuid(),
        ];
    }

    public function isValid(): bool
    {
        return new Carbon($this->created_at)
            ->diffInMinutes(Carbon::now()) <= 15;
    }

    public function url(): string
    {
        return config('app-url').'/'.route('verify').'?token='.$this->token;
    }
}
