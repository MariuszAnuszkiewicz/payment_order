<?php

namespace App\Helpers;

use SimpleXMLElement;

trait XmlHelper
{
    /**
     * Converts PHP array to XML.
     *
     * @param  array             $data     an array to be converted
     * @param  SimpleXMLElement &$xmlData can be: new SimpleXMLElement('<?xml version="1.0"?><data></data>')
     * @return void
     */
    private function arrayToXml(array $data, SimpleXMLElement &$xmlData): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item'. $key;
                }
                $subNode = $xmlData->addChild($key);
                $this->arrayToXml($value, $subNode);
            } else {
                $xmlData->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }
}
