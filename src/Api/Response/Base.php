<?php

declare(strict_types=1);

namespace Obud\FioSigner\Api\Response;

use Obud\FioSigner\Api\ResponseException;
use ReflectionClass;
use ReflectionNamedType;
use SimpleXMLElement;


abstract class Base
{
    protected SimpleXMLElement $__xml;


    public function __construct(string $xmlResponse)
    {
        $r = new ReflectionClass($this);

        $xml = simplexml_load_string($xmlResponse);
        if ($xml === false) {
            throw new ResponseException('Response Parse Error.');
        }
        $this->__xml = $xml;

        if ($xml->getName() === 'Error') {
            throw new ResponseException('Response Error. ' . ((array)$xml)['@attributes']['description']);
        }
        if ($xml->getName() !== $r->getShortName()) {
            throw new ResponseException('Expected ' . $r->getShortName() . ' as response. ' . $xmlResponse);
        }

        $attrs = ((array)$xml)['@attributes'];
        foreach ($attrs as $attrName => $attrValue) {
            if ($r->hasProperty($attrName)) {
                $property = $r->getProperty($attrName);
                if (!$property->getType() instanceof ReflectionNamedType) {
                    continue;
                }
                if ($property->getType()->getName() === 'string') {
                    $this->$attrName = $attrValue;
                }
                if ($property->getType()->getName() === 'int') {
                    $this->$attrName = (int)$attrValue;
                }
            }
        }
    }

}