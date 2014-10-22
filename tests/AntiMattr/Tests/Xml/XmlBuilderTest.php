<?php

namespace AntiMattr\Tests\Xml;

use AntiMattr\Xml\XmlBuilder;
use AntiMattr\TestCase\AntiMattrTestCase;

class XmlBuilderTest extends AntiMattrTestCase
{
    private $builder;

    protected function setUp()
    {
        $this->builder = new XMLBuilderStub();
    }

    public function testConstructor()
    {
        $this->assertInstanceof('AntiMattr\Xml\XmlBuilderInterface', $this->builder);
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

    /**
     * @dataProvider provideRestructureData
     */
    public function testRestructure($errorMessage, $data, $expectedData)
    {
        $data = $this->builder->doRestructure($data);
        $this->assertEquals($expectedData, $data, $errorMessage);
    }

    public function provideRestructureData()
    {
        return array(
            array(
                'No Structure provided',
                // Given
                array(
                    2 => 'string'
                ),
                // Transformed
                array(
                    2 => array(
                        '_name' => 2,
                        '_values' => 'string'
                    )
                )
            ),
            array(
                'Structure provided',
                // Given
                array(
                    'product' => array(
                        '_name' => 'product',
                        '_values' => 'string'
                    )
                ),
                // Transformed
                array(
                    'product' => array(
                        '_name' => 'product',
                        '_values' => 'string'
                    )
                )
            ),
            array(
                'Some structure provided',
                // Given
                array(
                    2 => 'string',
                    'product' => array(
                        '_name' => 'product',
                        '_values' => 'string'
                    )
                ),
                // Transformed
                array(
                    2 => array(
                        '_name' => 2,
                        '_values' => 'string'
                    ),
                    'product' => array(
                        '_name' => 'product',
                        '_values' => 'string'
                    )
                )
            ),
            array(
                'No Structure and nested arrays',
                // Given
                array(
                    2 => 'string',
                    3 => array(
                        0 => 'string3',
                        1 => 'string4'
                    )
                ),
                // Transformed
                array(
                    2 => array(
                        '_name' => 2,
                        '_values' => 'string'
                    ),
                    3 => array(
                        '_name' => 3
                    )
                )
            ),
            array(
                'No Structure and deep nested arrays',
                // Given
                array(
                    2 => 'string',
                    3 => array(
                        0 => 'string3',
                        1 => array(
                            0 => 'string4',
                            1 => 'string5'
                        ),
                        2 => 'string6'
                    ),
                    4 => array(
                        0 => 'string7',
                        1 => 'string8'
                    )
                ),
                // Transformed
                array(
                    2 => array(
                        '_name' => 2,
                        '_values' => 'string'
                    ),
                    3 => array(
                        '_name' => 3
                    ),
                    4 => array(
                        '_name' => 4
                    )
                )
            ),
            array(
                'Structure and deep nested arrays',
                // Given
                array(
                    2 => array(
                        '_name' => 2,
                        '_values' => 'string'
                    ),
                    3 => array(
                        '_name' => 3,
                        '_values' => array(
                            0 => 'string3',
                            1 => array(
                                0 => 'string4',
                                1 => 'string5'
                            ),
                            2 => 'string6'
                        )
                    ),
                    4 => array(
                        '_name' => 4,
                        '_values' => array(
                            0 => 'string7',
                            1 => 'string8'
                        )
                    )
                ),
                // Transformed
                array(
                    2 => array(
                        '_name' => 2,
                        '_values' => 'string'
                    ),
                    3 => array(
                        '_name' => 3,
                        '_values' => array(
                            0 => 'string3',
                            1 => array(
                                0 => 'string4',
                                1 => 'string5'
                            ),
                            2 => 'string6'
                        )
                    ),
                    4 => array(
                        '_name' => 4,
                        '_values' => array(
                            0 => 'string7',
                            1 => 'string8'
                        )
                    )
                )
            ),
        );
    }

    public function testAddOneNode()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/01_one_node.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            0 => array('_name' => 'product')
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }

    public function testAddMultipleNodes()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/02_multiple_nodes.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            0 => array('_name' => 'product'),
            1 => array('_name' => 'product'),
            2 => array('_name' => 'product')
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }

    public function testAddMultipleNodesWithSkippedIndexes()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/02_multiple_nodes.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            0 => array('_name' => 'product'),
            88 => array('_name' => 'product'),
            1000 => array('_name' => 'product')
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }

    public function testAddMultipleNodesWithDifferentNames()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/04_multiple_nodes_with_different_names.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            0 => array('_name' => 'product'),
            1 => array('_name' => 'foo'),
            2 => array('_name' => 'bar')
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }

    public function testAddOneNodeFromAssociativeArray()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/05_one_node_from_associative_array.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            'product' => array()
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }

    public function testAddAssociativeArrayWithAttributes()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/06_associative_array_with_attributes.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            'product' => array(
                '_attributes' => array('foo' => 'bar', 'example' => 'test')
            )
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }

    public function testAddIndexedArrayWithAttributes()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/06_associative_array_with_attributes.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            0 => array(
                '_name' => 'product',
                '_attributes' => array('foo' => 'bar', 'example' => 'test')
            )
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }

    public function testAddAssociativeAndIndexedArrayWithAttributes()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/08_associative_and_indexed_array_with_attributes.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            'product' => array(
                '_attributes' => array('foo1' => 'bar1', 'example1' => 'test1')
            ),
            0 => array(
                '_name' => 'product',
                '_attributes' => array('foo2' => 'bar2', 'example2' => 'test2')
            )
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }

    public function testAddAssociativeAndIndexedArrayWithAttributesReversed()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/09_associative_and_indexed_array_with_attributes_reversed.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            0 => array(
                '_name' => 'product',
                '_attributes' => array('foo2' => 'bar2', 'example2' => 'test2')
            ),
            'product' => array(
                '_attributes' => array('foo1' => 'bar1', 'example1' => 'test1')
            )
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }

    public function testAddOneSubnode()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/11_one_subnode.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            0 => array(
                '_name' => 'product',
                '_values' => array(
                    0 => array('_name' => 'item')
                )
            )
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }

    public function testAddMultipleSubnodes()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/12_multiple_subnodes.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            0 => array(
                '_name' => 'product',
                '_values' => array(
                    0 => array('_name' => 'item'),
                    1 => array('_name' => 'item'),
                    2 => array('_name' => 'item')
                )
            )
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }

    public function testAddMultipleSubnodesWithSkippedIndexes()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/12_multiple_subnodes.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            0 => array(
                '_name' => 'product',
                '_values' => array(
                    0 => array('_name' => 'item'),
                    99 => array('_name' => 'item'),
                    3000 => array('_name' => 'item')
                )
            )
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }

    public function testAddMultipleSubnodesWithDifferentNames()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/14_multiple_subnodes_with_different_names.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            0 => array(
                '_name' => 'product',
                '_values' => array(
                    0 => array('_name' => 'item'),
                    1 => array('_name' => 'foo'),
                    2 => array('_name' => 'bar')
                )
            )
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }

    public function testAddOneSubnodeWithAssociativeArray()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/15_one_subnode_from_associative_array.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            0 => array(
                '_name' => 'product',
                '_values' => array(
                    'item' => array(),
                )
            )
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }

    public function testAddSubnodeAssociativeArrayWithAttributes()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/16_subnode_associative_array_with_attributes.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            0 => array(
                '_name' => 'product',
                '_values' => array(
                    'item' => array(
                        '_attributes' => array('foo5' => 'bar5', 'example5' => 'test5')
                    ),
                )
            )
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }

    public function testAddSubnodeIndexedArrayWithAttributes()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/16_subnode_associative_array_with_attributes.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            0 => array(
                '_name' => 'product',
                '_values' => array(
                    0 => array(
                        '_name' => 'item',
                        '_attributes' => array('foo5' => 'bar5', 'example5' => 'test5')
                    ),
                )
            )
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }

    public function testAddSubnodeAssociativeAndIndexedArrayWithAttributes()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/18_subnode_associative_and_indexed_array_with_attributes.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            0 => array(
                '_name' => 'product',
                '_values' => array(
                    'item' => array(
                        '_attributes' => array('foo10' => 'bar10', 'example10' => 'test10')
                    ),
                    0 => array(
                        '_name' => 'item',
                        '_attributes' => array('foo11' => 'bar11', 'example11' => 'test11')
                    ),
                )
            )
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }

    public function testAddSubnodeAssociativeAndIndexedArrayWithAttributesReversed()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/19_subnode_associative_and_indexed_array_with_attributes_reversed.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            0 => array(
                '_name' => 'product',
                '_values' => array(
                    0 => array(
                        '_name' => 'item',
                        '_attributes' => array('foo11' => 'bar11', 'example11' => 'test11')
                    ),
                    'item' => array(
                        '_attributes' => array('foo10' => 'bar10', 'example10' => 'test10')
                    ),
                )
            )
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }

    public function testAddOneNodeWithContent()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/21_one_node_with_content.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            0 => array('_name' => 'product', '_values' => 'test')
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }

    public function testAddMultipleNodesWithContent()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/22_multiple_nodes_with_content.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            0 => array('_name' => 'product', '_values' => 'test1'),
            1 => array('_name' => 'product', '_values' => 'test2'),
            2 => array('_name' => 'product', '_values' => 'test3')
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }

    public function testAddMultipleNodesWithSkippedIndexesWithContent()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/22_multiple_nodes_with_content.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            0 => array('_name' => 'product', '_values' => 'test1'),
            88 => array('_name' => 'product', '_values' => 'test2'),
            1000 => array('_name' => 'product', '_values' => 'test3')
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }

    public function testAddMultipleNodesWithDifferentNamesWithContent()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/24_multiple_nodes_with_different_names_with_content.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            0 => array('_name' => 'product', '_values' => 'test1'),
            1 => array('_name' => 'foo', '_values' => 'test2'),
            2 => array('_name' => 'bar', '_values' => 'test3'),
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }

    public function testAddOneNodeFromAssociativeArrayWithContent()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/25_one_node_from_associative_array_with_content.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            'product' => 'test 25'
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }

    public function testAddAssociativeArrayWithAttributesWithContent()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/26_associative_array_with_attributes_with_content.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            'product' => array(
                '_attributes' => array('foo' => "bar", 'example' => "test"),
                '_values' => 'test 26'
            )
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }

    public function testAddIndexedArrayWithAttributesWithContent()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/26_associative_array_with_attributes_with_content.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            0 => array(
                '_name' => 'product',
                '_attributes' => array('foo' => "bar", 'example' => "test"),
                '_values' => 'test 26'
            )
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }

    public function testAddAssociativeAndIndexedArrayWithAttributesWithContent()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/28_associative_and_indexed_array_with_attributes_with_content.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            'product' => array(
                '_attributes' => array('foo1' => "bar1", 'example1' => "test1"),
                '_values' => 'test 28a'
            ),
            0 => array(
                '_name' => 'product',
                '_attributes' => array('foo2' => "bar2", 'example2' => "test2"),
                '_values' => 'test 28b'
            )
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }

    public function testAddAssociativeAndIndexedArrayWithAttributesWithContentReversed()
    {
        $expectedXml = file_get_contents(dirname(__DIR__).'/Resources/29_associative_and_indexed_array_with_attributes_with_content_reversed.xml');

        $root = $this->builder
            ->setRoot('root')
            ->create();

        $data = array(
            0 => array(
                '_name' => 'product',
                '_attributes' => array('foo2' => "bar2", 'example2' => "test2"),
                '_values' => 'test 28b'
            ),
            'product' => array(
                '_attributes' => array('foo1' => "bar1", 'example1' => "test1"),
                '_values' => 'test 28a'
            )
        );
        $this->builder->add($root, $data);

        $xml = $root->asXML();
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml);
    }
}

class XMLBuilderStub extends XMLBuilder
{
    public function doRestructure($data)
    {
        return $this->restructure($data);
    }
}
