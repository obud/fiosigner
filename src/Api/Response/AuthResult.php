<?php

declare(strict_types=1);

namespace Obud\FioSigner\Api\Response;


class AuthResult extends Base
{
    protected string $status;


    public function getStatus(): string
    {
        return $this->status;
    }

}