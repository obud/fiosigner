<?php

declare(strict_types=1);

namespace Obud\FioSigner\Api\Request;


class OrderSignRequest extends Base
{
    protected string $id;

    protected string $action;

    protected string|null $sign;

    protected string|null $certFingerprint;


    public function __construct(int $id, string $action, string $sign = null, string $certFingerprint = null)
    {
        $this->id = (string)$id;
        $this->action = $action;
        $this->sign = $sign;
        $this->certFingerprint = $certFingerprint;
    }


    public function getId(): string
    {
        return $this->id;
    }


    public function getAction(): string
    {
        return $this->action;
    }


    public function getSign(): ?string
    {
        return $this->sign;
    }


    public function getCertFingerprint(): ?string
    {
        return $this->certFingerprint;
    }


}