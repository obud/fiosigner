<?php

declare(strict_types=1);

namespace Obud\FioSigner\Api\Request;

use Obud\FioSigner\Api\Xml;
use ReflectionClass;


abstract class Base
{

    public function __toString(): string
    {
        $r = new ReflectionClass($this);
        $dom = Xml::init();
        $root = $dom->createElement($r->getShortName());

        $props = $r->getProperties();
        foreach ($props as $prop) {
            if ($this->{$prop->getName()} !== null) {
                $root->setAttribute($prop->getName(), $this->{$prop->getName()});
            }
        }
        $dom->appendChild($root);

        return Xml::render($dom);
    }

}