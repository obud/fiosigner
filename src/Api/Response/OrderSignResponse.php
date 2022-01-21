<?php

declare(strict_types=1);

namespace Obud\FioSigner\Api\Response;


class OrderSignResponse extends Base
{
    protected int $id;

    protected string $status;


    public function getId(): int
    {
        return $this->id;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

}