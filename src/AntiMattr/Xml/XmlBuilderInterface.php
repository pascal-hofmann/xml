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
interface XmlBuilderInterface
{
    /**
     * @param string $encoding
     *
     * @return \AntiMattr\Xml\XmlBuilderInterface
     */
    public function setEncoding($encoding);

    /**
     * @param string $namespace
     *
     * @return \AntiMattr\Xml\XmlBuilderInterface
     */
    public function setNamespace($namespace);

    /**
     * @param string $root
     *
     * @return \AntiMattr\Xml\XmlBuilderInterface
     */
    public function setRoot($root);

    /**
     * @param string $schemaLocation
     *
     * @return \AntiMattr\Xml\XmlBuilderInterface
     */
    public function setSchemaLocation($schemaLocation);

    /**
     * @param string $version
     *
     * @return \AntiMattr\Xml\XmlBuilderInterface
     */
    public function setVersion($version);

    /**
     * @return \SimpleXMLElement $element
     */
    public function create();

    /**
     * @param SimpleXMLElement $parent
     * @param array            $data
     *
     * @throws \RuntimeException
     */
    public function add(SimpleXMLElement $parent, array $data);
}
