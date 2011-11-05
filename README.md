Provided Abstracts
------------------
* **Edp\Common\Model** A base class for models which provides fromArray() and
  fromConfig() static methods, useful for constructing models from database
  result sets. It also converts underscored field names such as `user_id` to
  camel-cased property names such as `userId`.
* **Edp\Common\DbMapper** A base class for model mappers that allows for easy
  separation of read and write queries to master/slave adapters.
