<?php

declare(strict_types=1);

namespace Obud\FioSigner\Api\Request;


class Hello extends Base
{
    protected string $username;

    protected string $protocolVersion = '1.0';

    protected string $publicKeyFingerprint;

    protected string $proxyType = 'NONE';

    protected string $proxyValue = '';


    public function __construct(string $username, string $publicKeyFingerprint)
    {
        $this->username = $username;
        $this->publicKeyFingerprint = $publicKeyFingerprint;
    }


    public function getUsername(): string
    {
        return $this->username;
    }


    public function getPublicKeyFingerprint(): string
    {
        return $this->publicKeyFingerprint;
    }

}