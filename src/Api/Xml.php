<?php

declare(strict_types=1);

namespace Obud\FioSigner\Api;

use DOMDocument;


class Xml
{

    public static function init(): DOMDocument
    {
        $dom = new DOMDocument();
        $dom->encoding = 'utf-8';
        $dom->xmlVersion = '1.0';
        $dom->xmlStandalone = true;
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;

        return $dom;
    }


    public static function render(DOMDocument $dom): string
    {
        $result = $dom->saveXML();
        if ($result === false) {
            throw new RequestException('Creating XML Request Exception');
        }

        return str_replace(PHP_EOL, '', $result);
    }

}