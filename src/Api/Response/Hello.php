<?php

declare(strict_types=1);

namespace Obud\FioSigner\Api\Response;


class Hello extends Base
{
    protected string $version;


    public function getVersion(): string
    {
        return $this->version;
    }

}