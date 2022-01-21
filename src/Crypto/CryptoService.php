<?php

declare(strict_types=1);

namespace Obud\FioSigner\Crypto;

use OpenSSLAsymmetricKey;


class CryptoService
{
    private OpenSSLAsymmetricKey $privateKey;

    private string $publicKey;


    public function __construct(
        string $privateKeyFile,
        string $publicKeyFile,
        string $passphrase = '',
    )
    {
        $privateKey = openssl_pkey_get_private('file://' . $privateKeyFile, $passphrase);
        if (!$privateKey) {
            throw new CryptoException('Unlock Private Key Exception');
        }

        $this->privateKey = $privateKey;
        $publicKey = file_get_contents($publicKeyFile);
        if ($publicKey === false) {
            throw new CryptoException('Load Public Key Exception');
        }

        $this->publicKey = $publicKey;
    }


    public function sign(string $data): string
    {
        $result = openssl_sign($data, $signature, $this->privateKey, OPENSSL_ALGO_SHA256);
        if (!$result) {
            throw new CryptoException('Signature Failure Exception');
        }

        return base64_encode($signature);
    }


    public function getPublicKeyFingerprint(): string
    {
        $keyWithoutPemWrapper = preg_replace(
            '/^-----BEGIN (?:[A-Z]+ )?PUBLIC KEY-----([A-Za-z0-9\\/\\+\\s=]+)-----END (?:[A-Z]+ )?PUBLIC KEY-----$/ms',
            '\\1',
            $this->publicKey,
        );
        if ($keyWithoutPemWrapper === null) {
            throw new CryptoException('Read Public Key Exception');
        }
        $keyDataWithoutSpaces = preg_replace('/\\s+/', '', $keyWithoutPemWrapper);
        if ($keyDataWithoutSpaces === null) {
            throw new CryptoException('Read Public Key Exception');
        }
        $binaryKey = base64_decode($keyDataWithoutSpaces);

        return hash('sha256', $binaryKey);
    }

}