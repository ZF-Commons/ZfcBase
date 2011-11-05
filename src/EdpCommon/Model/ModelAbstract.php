<?php

namespace EdpCommon\Model;

use Zend\Stdlib\IsAssocArray,
    Zend\Config\Config;

abstract class ModelAbstract
{
    const ARRAYSET_PRESERVE_KEYS    = 0;
    const ARRAYSET_RESET_KEYS       = 1;

    /**
     * Convert an array to an instance of a model class 
     * 
     * @param array $array 
     * @return Edp\Common\Model
     */
    public static function fromArray($array)
    {
        if (!IsAssocArray::test($array)) {
            return false;
            //throw new \Exception('Error: Edp\Common\Model::fromArray expects associative array.');
        }
        $classMethods = get_class_methods(get_called_class());
        $model = new static();
        foreach ($array as $key => $value) {
            $setter = 'set' . implode('',array_map('ucfirst',explode('_',strtolower($key))));
            if (in_array($setter, $classMethods)) {
                $model->$setter($value);
            }
        }
        return $model;
    }

    /**
     * Convert an instance of Zend\Config\Config to an instance of a model class
     * 
     * @param Config $config 
     * @return Edp\Common\Model
     */
    public static function fromConfig(Config $config)
    {
        return static::fromArray($config->toArray());
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
}
