<?php

namespace ZfcBase\Mapper;

use DateTime;
use ReflectionProperty;
use Zend\Stdlib\Hydrator\HydratorInterface;

class MappingHydrator implements HydratorInterface
{
    /**
     * @var array
     */
    protected $map;

    /**
     * Constructor
     *
     * @param array $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    /**
     * Extract values from an object
     *
     * @param  object $object
     * @return array
     */
    public function extract($object)
    {
        $data = array();
        foreach ($this->map as $propertyName => $field) {
            switch ($field['type']) {
                case 'string':
                case 'integer':
                    $value = $this->extractField($object, $propertyName);
                    break;
                case 'datetime':
                    $value = $this->extractField($object, $propertyName);
                    if ($value instanceof DateTime) { // value could be null, so better check
                        $value = $value->format('Y-m-d H:i:s');
                    }
                    break;
                case 'bool':
                case 'boolean':
                    $value = (int) $this->extractField($object, $propertyName);
                    break;
                default:
                    continue;
            }
            $data[$field['name']] = $value;
        }
        return $data;
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array $data
     * @param  object $object
     * @return object
     */
    public function hydrate(array $data, $object)
    {
        foreach ($data as $field => $value) {
            foreach($this->map as $propertyName => $fieldInfo) {
                if ($field === $fieldInfo['name']) {
                    switch ($fieldInfo['type']) {
                        case 'string':
                        case 'integer':
                            $this->pushField($object, $propertyName, $value);
                            break;
                        case 'datetime':
                            $this->pushField($object, $propertyName, new DateTime($value));
                            break;
                        case 'bool':
                        case 'boolean':
                            $this->pushField($object, $propertyName, (bool) $value);
                            break;
                        default:
                            continue;
                    }
                }
            }
        }
        return $object;
    }

    /**
     * @param string $propertyName
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    public function getFieldForProperty($propertyName)
    {
        if (!isset($this->map[$propertyName])) {
            throw new Exception\InvalidArgumentException('Invalid property name');
        }
        return $this->map[$propertyName]['name'];
    }

    /**
     * @param string $fieldName
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    public function getPropertyForField($fieldName)
    {
        foreach ($this->map as $propertyName => $fieldInfo) {
            if ($fieldName === $fieldInfo['name']) {
                return $propertyName;
            }
        }
        throw new Exception\InvalidArgumentException('Invalid field name');
    }

    /**
     * @param object $object
     * @param string $propertyName
     */
    protected function extractField($object, $propertyName)
    {
        $property = new ReflectionProperty(get_class($object), $propertyName);
        $property->setAccessible(true);
        return $property->getValue($object);
    }

    /**
     * @param object $object
     * @param string $propertyName
     * @param mixed $value
     * @return void
     */
    protected function pushField($object, $propertyName, $value)
    {
        $property = new ReflectionProperty(get_class($object), $propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

}