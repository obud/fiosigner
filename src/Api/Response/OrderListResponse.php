<?php

declare(strict_types=1);

namespace Obud\FioSigner\Api\Response;

use SimpleXMLElement;


class OrderListResponse extends Base
{
    protected int $availableCount;

    /**
     * @var array<int, Order>
     */
    protected array $orders = [];


    public function __construct(string $xmlResponse)
    {
        parent::__construct($xmlResponse);

        foreach ($this->__xml as $element) {
            /** @var SimpleXMLElement $element */
            if ($element->getName() === 'Order') {
                $this->orders[] = new Order((int)$element->attributes()?->id, (string)$element[0]);
            }
            if ($element->getName() === 'Batch' && is_iterable($element[0])) {
                foreach($element[0] as $batchElement) {
                    /** @var SimpleXMLElement $batchElement */
                    if ($batchElement->getName() === 'Order') {
                        $this->orders[] = new Order((int)$batchElement->attributes()?->id, (string)$batchElement[0]);
                    }
                }
            }
        }
    }

    public function getAvailableCount(): int
    {
        return $this->availableCount;
    }

    /**
     * @return array<int, Order>
     */
    public function getOrders(): array
    {
        return $this->orders;
    }

}