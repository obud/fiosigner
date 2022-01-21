<?php

declare(strict_types=1);

namespace Obud\FioSigner\Api\Response;


class AuthToken extends Base
{
    protected string $challenge;


    public function getChallenge(): string
    {
        return $this->challenge;
    }

}