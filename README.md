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

There are a lot of examples in the tests directory, here are the basics

Note: Ordinary Arrays are supported. To handle scenarios such as "attributes" and "indexed vs associative arrays", notice the existince of array keys

```text
'_name'
'_attributes'
'_values'
```

##### One Node

```xml
<?xml version="1.0" encoding="UTF-8"?>
<root>
  <product/>
</root>
```

```php
$root = $this->builder
    ->setRoot('root')
    ->create();

$data = array(
    'product' => array()
);
$this->builder->add($root, $data);

$xml = $root->asXML();
```

##### Multiple Nodes

```xml
<?xml version="1.0" encoding="UTF-8"?>
<root>
  <product/>
  <foo/>
  <bar/>
</root>
```

```php
$root = $this->builder
    ->setRoot('root')
    ->create();

$data = array(
    'product' => array(),
    'foo' => array(),
    'bar' => array()
);
$this->builder->add($root, $data);

$xml = $root->asXML();
```

##### Multiple Repeating Nodes

```xml
<?xml version="1.0" encoding="UTF-8"?>
<root>
  <product/>
  <product/>
  <product/>
</root>
```

```php
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
```

##### Child Nodes and Attributes

```xml
<?xml version="1.0" encoding="UTF-8"?>
<root>
  <product>
  	<item foo5="bar5" example5="test5"/>
  </product>
</root>
```

```php
$root = $this->builder
    ->setRoot('root')
    ->create();

$data = array(
    'product' => array(
        '_values' => array(
            'item' => array(
                '_attributes' => array('foo5' => 'bar5', 'example5' => 'test5')
            ),
        )
    )
);
$this->builder->add($root, $data);
$xml = $root->asXML();
```

Pull Requests
=============

Pull Requests - PSR Standards
-----------------------------

Please use the pre-commit hook to fix all code to PSR standards

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
```
