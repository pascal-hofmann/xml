<?php

namespace AntiMattr\Tests\Xml;

use AntiMattr\Xml\XmlBuilder;
use AntiMattr\TestCase\AntiMattrTestCase;

class XmlBuilderTest extends AntiMattrTestCase
{
    private $builder;

    protected function setUp()
    {
        $this->builder = new XMLBuilder();
    }

    public function testCreate()
    {
        $this->builder
            ->setRoot('api-response')
            ->setVersion('1.0')
            ->setEncoding('UTF-8')
            ->setNamespace('http://seller.marketplace.sears.com/inventory/v1')
            ->setSchemaLocation('http://seller.marketplace.sears.com/inventory/v1 dss-inventory.xsd ');

        $expectedElement = $this->builder->create();

        $this->assertInstanceof('SimpleXMLElement', $expectedElement);

        $this->assertEquals('api-response', $expectedElement->getName());

        $xml = $expectedElement->asXML();

        $this->assertEquals(0, preg_match('/version="2.0"/', $xml));
        $this->assertEquals(1, preg_match('/version="1.0"/', $xml));

        $this->assertEquals(0, preg_match('/encoding="ISO"/', $xml));
        $this->assertEquals(1, preg_match('/encoding="UTF-8"/', $xml));
    }

}
