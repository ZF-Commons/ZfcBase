<?php

namespace ZfcBase\Model;

use Zend\Stdlib\ArrayUtils,
    Zend\Db\ResultSet\RowObjectInterface,
    DateTime,
    InvalidArgumentException,
    ArrayAccess,
    ZfcBase\Util\String;

abstract class ModelAbstract implements ArrayAccess, RowObjectInterface
{
    protected $exts = array();
    const ARRAYSET_PRESERVE_KEYS    = 0;
    const ARRAYSET_RESET_KEYS       = 1;

    /**
     * Convert an array to an instance of a model class
     *
     * @param array $array
     * @return ZfcBase\Model\ModelAbstract
     */
    public static function fromArray($array)
    {
        if (!ArrayUtils::hasStringKeys($array)) {
            throw new InvalidArgumentException('ModelAbstract::fromArray() expects associative array.');
        }
        $model = new static();
        $model->exchangeArray($array);
        return $model;
    }

    /**
     * Convert an array of arrays into an array of model classes
     *
     * @param array $array
     * @param int $mode
     * @return array
     */
    public static function fromArraySet(array $array, $mode = self::ARRAYSET_PRESERVE_KEYS)
    {
        $return = array();
        foreach ($array as $key => $value) {
            if ($mode === self::ARRAYSET_PRESERVE_KEYS) {
                $return[$key] = static::fromArray($value);
            } else if ($mode == self::ARRAYSET_RESET_KEYS) {
                $return[] = static::fromArray($value);
            }
        }
        return $return;
    }

    public function ext($extension, $value = null)
    {
        if (null !== $value) {
            $this->exts[$extension] = $value;
        }
        if (!isset($this->exts[$extension])) {
            return null;
        }
        return $this->exts[$extension];
    }

    //matuszemi: we keep both interfaces for now - Zend\Db\ResultSet\RowObjectInterface
    public function setRowData(array $array) {
        $this->exchangeArray($array);
    }
    
    public function exchangeArray($array) {
        foreach ($array as $key => $value) {
            $setter = static::fieldToSetterMethod($key);
            if (is_callable(array($this, $setter))) {
                $this->$setter($value);
            }
        }
    }
    //END
    
    /**
     * Convert a model class to an array recursively
     *
     * @param mixed $array
     * @return array
     */
    public function toArray($array = false)
    {
        $array = $array ?: get_object_vars($this);
        foreach ($array as $key => $value) {
            unset($array[$key]);
            $getter = static::fieldToGetterMethod($key);
            if (is_callable(array($this, $getter))) {
                $value = $this->$getter();
            }
            if (is_object($value)) {
                if (is_callable(array($value, 'toArray'))) {
                    $array[$key] = $value->toArray();
                } else {
                    $array[$key] = $this->toArrayObject($value);
                }
            } elseif (is_array($value) && count($value) > 0) {
                $array[$key] = $this->toArray($value);
            } elseif ($value !== NULL && !is_array($value)) {
                $array[$key] = $value;
            }
        }
        return $array;
    }
    
    protected function toArrayObject($value) {
        //matuszemi: we do not convert objects to string - considering toStringValueArray()
//        if($value instanceof DateTime) {
//            return $value->format('Y-m-d H:i:s'); // meh...
//        }
        
        return $value;
    }
    
    public function count() {
        $vars = get_object_vars($this);
        unset($vars['exts']);
        return count($vars);
    }
    
    public function offsetExists($key) {
        $getter = self::fieldToGetterMethod($key);
        if(is_callable(array($this, $getter))) {
            return true;
        }
        
        return false;
    }

    public function offsetGet($key) {
        $getter = self::fieldToGetterMethod($key);
        if(is_callable(array($this, $getter))) {
            return $this->$getter();
        }
        
        return null;
    }

    public function offsetSet($key, $value) {
        $setter = static::fieldToSetterMethod($key);
        if(!is_callable(array($this, $setter))) {
            throw new \Exception("offsetSet: $setter() does not exist");
        }
        
        return $this->$setter($value);
    }
    
    public function offsetUnset($key) {
        throw new \Exception("offsetUnset n/i");
    }
    
    public static function fieldToSetterMethod($name)
    {
        return 'set' . String::toCamelCase($name);
    }

    public static function fieldToGetterMethod($name)
    {
        return 'get' . String::toCamelCase($name);
    }

}
