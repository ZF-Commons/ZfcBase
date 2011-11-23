EdpCommon
=========
Version 0.0.1 Created by Evan Coury

Introduction
------------
EdpCommon provides a suite of common classes used across several ZF2 modules.
You probably don't need to install this module unless either A) you are
installing a module that depends on EdpCommon, or B) you are building a module
that depends on EdpCommon.

Requirements
------------

* Zend Framework 2

Installation
------------
Simply clone this project into your `./vendors/` directory and enable it in your
`application.config.php` file.


Provided Classes
----------------

* `Zend\Db\Adapter\DiPdoMysql` - An extended version of the PDO MySQL
  adapter for Zend\Db which allows for injecting an existing PDO instance in a
  DI-friendly way. Under the Zend namespae simply so I don't forget that this is
  simply a hack until the Zend\Db refactoring is complete.
* `EdpCommon\Mapper\DbMapperAbstract` - An abstract mapper for Zend\Db that
  allows for different read and write DB connections (master/slave). 
* `EdpCommon\Model\ModelAbstract` - An abstract model class with factory
  methods for instantiating from an associative array (database result). 
* `EdpCommon\Form\ProvidesEventsForm` - Extends Zend\Form and provides the
  functionality of `EdpCommon\EventManager\EventProvider` (basically it's a
  crutch since we can't use traits yet).
* `EdpCommon\EventManager\EventProvider` - Abstract class that gives extending
  classes an event manager and related methods.
* `EdpCommon\Util\String` - Commonly used string functions. Currently this only
  has a single method, getRandomBytes(), which provides a cross-platform way to
  get psuedo-random bytes suitable for security use.
