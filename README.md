ZfcBase
=========
Version 0.0.1 Created by Evan Coury and the ZF-Commons team

Introduction
------------
ZfcBase provides a suite of common classes used across several ZF2 modules.
You probably don't need to install this module unless either A) you are
installing a module that depends on ZfcBase, or B) you are building a module
that depends on ZfcBase.

Requirements
------------

* Zend Framework 2

Installation
------------
Simply clone this project into your `./vendor/` directory and enable it in your
`application.config.php` file.


Provided Classes
----------------

* `Zend\Db\Adapter\DiPdoMysql` - An extended version of the PDO MySQL
  adapter for Zend\Db which allows for injecting an existing PDO instance in a
  DI-friendly way. Under the Zend namespae simply so I don't forget that this is
  simply a hack until the Zend\Db refactoring is complete.
* `ZfcBase\Mapper\DbMapperAbstract` - An abstract mapper for Zend\Db that
  allows for different read and write DB connections (master/slave).
* `ZfcBase\Model\ModelAbstract` - An abstract model class with factory
  methods for instantiating from an associative array (database result).
* `ZfcBase\Form\ProvidesEventsForm` - Extends Zend\Form and provides the
  functionality of `ZfcBase\EventManager\EventProvider` (basically it's a
  crutch since we can't use traits yet).
* `ZfcBase\EventManager\EventProvider` - Abstract class that gives extending
  classes an event manager and related methods.
* `ZfcBase\Util\String` - Commonly used string functions. Currently this only
  has a single method, getRandomBytes(), which provides a cross-platform way to
  get psuedo-random bytes suitable for security use. To be replaced by
  Zend\Crypt later.
