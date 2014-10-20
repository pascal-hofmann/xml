XML
===

The AntiMattr XML library that provides XML building support for SimpleXMLElement.

Installation
============

Use composer to install

```bash
composer install
```



Example
=======

```php
use AntiMattr\Xml\XmlBuilder;

$builder = new XmlBuilder();
$simpleXmlElement = $builder
    ->setRoot('api-response')
    ->setVersion('1.0')
    ->setEncoding('UTF-8')
    ->setNamespace('http://seller.marketplace.sears.com/inventory/v1')
    ->setSchemaLocation('http://seller.marketplace.sears.com/inventory/v1 dss-inventory.xsd')
    ->create();

$data = array(
	array(
		'_attributes' => array('foo' => 'bar') // _attributes are a constant targeting inline attributes
		'example' => 'value', // Primitive key / value pairs supported
		array( 
			'foo1', // Indexed arrays supported
			'foo2'
			'foo3'
		)
	),
    array(
		'_attributes' => array('foo' => 'bar')
		'example' => 'value2',
		array( // 
			'stringKey1' => 'foo1', // Hash based arrays supported
		    'stringKey2' => 'foo2'
			'stringKey3' => 'foo3'
		)
	)
);

$childXmlElement = $builder->addChild($simpleXmlElement, 'item-example', $data);
```

Pull Requests
=============

Pull Requests - PSR Standards
-----------------------------

Please use the pre-commit hook to run the fix all code to PSR standards

Install once with

```bash
./bin/install.sh 
Copying /antimattr-xml/bin/pre-commit.sh -> /antimattr-xml/bin/../.git/hooks/pre-commit
```

Pull Requests - Testing
-----------------------

Please make sure tests pass

```bash
$ vendor/bin/phpunit tests
```

Pull Requests - Code Sniffer and Fixer
--------------------------------------

Don't have the pre-commit hook running, please make sure to run the fixer/sniffer manually

```bash
$ vendor/bin/php-cs-fixer fix src/
$ vendor/bin/php-cs-fixer fix tests/
