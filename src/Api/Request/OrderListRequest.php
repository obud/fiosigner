<?php

declare(strict_types=1);

namespace Obud\FioSigner\Api\Request;


class OrderListRequest extends Base
{
    protected string $maxItems;


    public function __construct(int $maxItems)
    {
        $this->maxItems = (string)$maxItems;
    }


    public function getMaxItems(): int
    {
        return (int)$this->maxItems;
    }


}