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

use RuntimeException;
use SimpleXMLElement;

/**
 * @author Matthew Fitzgerald <matthewfitz@gmail.com>
 */
class XmlBuilder implements XmlBuilderInterface
{
    const ATTRIBUTES = '_attributes';
    const NAME = '_name';
    const RESTRUCTURED = '_restructured';
    const VALUES = '_values';

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
     * @param SimpleXMLElement $parent
     * @param array            $data
     *
     * @throws RuntimeException
     */
    public function add(SimpleXMLElement $parent, array $data)
    {
        // Restructure Data
        $data = $this->restructure($data);
        foreach ($data as $key => $node) {
            if (!isset($node[static::NAME])) {
                $message = sprintf(
                    "XmlBuilder Runtime Exception: Element data must be an array with an associative key of '%s'\nParent Element Name: %s\nChild Data: %s",
                    static::NAME,
                    $parent->getName(),
                    print_r($node, true)
                );
                throw new RuntimeException($message);
            }

            if (isset($node[static::ATTRIBUTES]) && !is_array($node[static::ATTRIBUTES])) {
                $message = sprintf(
                    "XmlBuilder Runtime Exception: Element data must be an array with an associative key of '%s' and the associative key must also be an array\nParent Element Name: %s\nChild Data: %s",
                    static::ATTRIBUTES,
                    $parent->getName(),
                    print_r($node, true)
                );
                throw new RuntimeException($message);
            }

            if (!isset($node[static::ATTRIBUTES]) && !isset($node[static::VALUES])) {
                $parent->addChild($node[static::NAME]);
                continue;
            }

            if (isset($node[static::VALUES]) && !is_array($node[static::VALUES])) {
                $child = $parent->addChild($node[static::NAME], htmlspecialchars($node[static::VALUES]));
            } else {
                $child = $parent->addChild($node[static::NAME]);
            }

            if (isset($node[static::ATTRIBUTES])) {
                foreach ($node[static::ATTRIBUTES] as $attributeName => $attributeValue) {
                    $child->addAttribute($attributeName, $attributeValue);
                }
            }

            if (!isset($node[static::VALUES])) {
                continue;
            }

            if (!is_array($node[static::VALUES])) {
                continue;
            }

            $childData = $node[static::VALUES];
            $this->add($child, $childData);
        }
    }

    /**
     * Structure of $value will be array(_name => '', _attributes => array(), '_values' => array())
     *
     * @param array $data
     *
     * @return array $data
     */
    protected function restructure($data)
    {
        foreach ($data as $key => $node) {
            if ($key === static::NAME || $key === static::ATTRIBUTES || $key === static::VALUES) {
                continue;
            }

            if (is_array($node) &&
                array_key_exists(static::NAME, $node) &&
                array_key_exists(static::ATTRIBUTES, $node) &&
                array_key_exists(static::VALUES, $node)) {
                continue;
            }

            $newValue = array();
            if (is_array($node)) {
                if (array_key_exists(static::ATTRIBUTES, $node)) {
                    $newValue[static::ATTRIBUTES] = $node[static::ATTRIBUTES];
                }
                if (array_key_exists(static::NAME, $node)) {
                    $newValue[static::NAME] = $node[static::NAME];
                }
            }

            if (!array_key_exists(static::NAME, $newValue)) {
                $newValue[static::NAME] = $key;
            }

            if (is_array($node) && array_key_exists(static::VALUES, $node)) {
                $newValue[static::VALUES] = $node[static::VALUES];
            } elseif (!is_array($node)) {
                $newValue[static::VALUES] = $node;
            }

            $data[$key] = $newValue;
        }

        return $data;
    }
}
