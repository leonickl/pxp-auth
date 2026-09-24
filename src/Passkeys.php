<?php

namespace PXP\Auth;

use Exception;
use lbuchs\WebAuthn\WebAuthn;
use PXP\Auth\Models\Identity;
use PXP\Auth\Models\Passkey;

class Passkeys
{
    private function relyingParty(): object
    {
        return (object) config('auth.relying-party');
    }

    private function authn(): WebAuthn
    {
        $relyingParty = $this->relyingParty();

        return new WebAuthn(
            $relyingParty->name,
            $relyingParty->id,
        );
    }

    private function credentialIds(Identity $user): array
    {
        return Passkey::forUser($user)
            ->map(fn (Passkey $passkey) => base64_decode($passkey->credential_id))
            ->toArray();
    }

    public function registerArgs(Identity $user): object
    {
        $authn = $this->authn();

        $args = $authn->getCreateArgs(
            userId: (string) $user->id,
            userName: $user->identifier(),
            userDisplayName: $user->name(),
            timeout: 30,
            requireResidentKey: true,
            requireUserVerification: true,
            excludeCredentialIds: $this->credentialIds($user),
        );

        session([
            'passkey' => [
                'challenge' => $authn->getChallenge()->getBinaryString(),
                'user_id' => $user->id,
            ],
        ]);

        return $args;
    }

    public function register(string $client, string $attest): array|string
    {
        $session = session()->array('passkey');

        try {
            $data = $this->authn()->processCreate(
                clientDataJSON: base64_decode($client),
                attestationObject: base64_decode($attest),
                challenge: $session['challenge'] ?? '',
                requireUserVerification: true,
                requireUserPresent: true,
                failIfRootMismatch: false,
            );
        } catch (Exception $e) {
            info($e->getMessage());

            return [
                'status' => 'error',
                'error' => 'error registering passkey',
            ];
        }

        $user = unstatic(resolve(Identity::class))
            ->findOrNull($session['user_id'] ?? null);

        if ($user === null) {
            return [
                'status' => 'error',
                'error' => 'user not found',
            ];
        }

        $credentialId = base64_encode($data->credentialId);

        if (Passkey::findByCredentialId($credentialId) !== null) {
            return [
                'status' => 'error',
                'error' => 'credential already registered',
            ];
        }

        Passkey::create(
            user_id: $user->id,
            credential_id: $credentialId,
            public_key: $data->credentialPublicKey,
            sign_count: $data->signatureCounter ?? 0,
        );

        session()->unset('passkey');

        return [
            'status' => 'ok',
        ];
    }

    public function validateArgs(): object
    {
        $authn = $this->authn();

        $args = $authn->getGetArgs([], 30);

        session([
            'passkey' => [
                'challenge' => $authn->getChallenge()->getBinaryString(),
            ],
        ]);

        return $args;
    }

    public function validate(
        string $id,
        string $client,
        string $auth,
        string $sig,
    ): array|string {
        $passkey = Passkey::findByCredentialId($id);

        if ($passkey === null) {
            return [
                'status' => 'error',
                'error' => 'invalid credentials',
            ];
        }

        $authn = $this->authn();

        try {
            $authn->processGet(
                clientDataJSON: base64_decode($client),
                authenticatorData: base64_decode($auth),
                signature: base64_decode($sig),
                credentialPublicKey: $passkey->public_key,
                challenge: $_SESSION['passkey']['challenge'] ?? '',
                prevSignatureCnt: $passkey->sign_count,
            );
        } catch (Exception $e) {
            info($e->getMessage());

            return [
                'status' => 'error',
                'error' => 'error validating passkey',
            ];
        }

        if ($authn->getSignatureCounter() !== null) {
            $passkey->fill(sign_count: $authn->getSignatureCounter())->save();
        }

        session()->unset('passkey');

        return [
            'status' => 'ok',
        ];
    }
}
