<?php

declare(strict_types=1);

namespace Obud\FioSigner\Api\Request;


class AuthSignature extends Base
{
    protected string $sign;

    protected string $certFingerprint;

    protected string $newKey = 'true';


    public function __construct(string $sign, string $certFingerprint)
    {
        $this->sign = $sign;
        $this->certFingerprint = $certFingerprint;
    }


    public function getSign(): string
    {
        return $this->sign;
    }


    public function getCertFingerprint(): string
    {
        return $this->certFingerprint;
    }

}