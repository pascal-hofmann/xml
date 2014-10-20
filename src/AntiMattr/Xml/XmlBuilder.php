<?php

/*
 * This file is part of the AntiMattr Xml Library, a library by Matthew Fitzgerald.
 *
 * (c) 2014 Matthew Fitzgerald
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AntiMattr\Xml;

use SimpleXMLElement;

/**
 * @author Matthew Fitzgerald <matthewfitz@gmail.com>
 */
class XmlBuilder
{
    const ATTRIBUTES = '_attributes';

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $encoding = 'UTF-8';

    /**
     * @var string
     */
    private $root = 'root';

    /**
     * @var string
     */
    private $schemaLocation;

    /**
     * @var string
     */
    private $version = '1.0';

    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;

        return $this;
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function setRoot($root)
    {
        $this->root = $root;

        return $this;
    }

    public function setSchemaLocation($schemaLocation)
    {
        $this->schemaLocation = $schemaLocation;

        return $this;
    }

    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return \SimpleXMLElement $element
     */
    public function create()
    {
        $definition = sprintf(
            '<?xml version="%s" encoding="%s"?><%s/>',
            $this->version,
            $this->encoding,
            $this->root
        );
        $element = new SimpleXMLElement($definition);

        if (null !== $this->namespace) {
            $element->addAttribute('xmlns', $this->namespace);
        }

        if (null !== $this->schemaLocation) {
            $element->addAttribute('xsi:schemaLocation', $this->schemaLocation, 'http://www.w3.org/2001/XMLSchema-instance');
        }

        return $element;
    }

    /**
     * @param  \SimpleXMLElement $element
     * @param  string            $name
     * @param  mixed             $values
     * @return \SimpleXMLElement $element
     */
    public function addChild($parent, $name, $values)
    {
        $child = $parent->addChild($name);
        $this->arrayToXML($values, $child);

        return $child;
    }

    /**
     * @param  array             $data
     * @param  \SimpleXMLElement $element
     * @return \SimpleXMLElement $element
     */
    protected function arrayToXml(array $data, SimpleXMLElement $element)
    {
        foreach ($data as $key => $value) {
            if ($key === self::ATTRIBUTES) {
                if (is_array($value)) {
                    foreach ($value as $attributeName => $attributeValue) {
                        $element->addAttribute($attributeName, $attributeValue);
                    }
                }
            } elseif (is_array($value)) {
                if (!is_numeric($key)) {
                    $subnode = $element->addChild("$key");
                    $this->arrayToXml($value, $subnode);
                } else {
                    $this->arrayToXml($value, $element);
                }
            } else {
                $element->addChild("$key",htmlspecialchars("$value"));
            }
        }
    }
}
