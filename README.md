Provided Classes
----------------
* `Zend\Db\Adapter\DiPdoMysql` - An extended version of the PDO MySQL
  adapter for Zend\Db which allows for injecting an existing PDO instance in a
  DI-friendly way.
* `EdpCommon\Mapper\DbMapperAbstract` - An abstract mapper for Zend\Db that
  allows for different read and write DB connections (master/slave). 
* `EdpCommon\Model\ModelAbstract` - An abstract model class with factory
  methods for instantiating from an associative array (database result). 
