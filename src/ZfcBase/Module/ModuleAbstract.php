<?php

namespace ZfcBase\Module;

use Zend\Module\Manager,
    Zend\Mvc\AppContext as Application,
    Zend\EventManager\StaticEventManager,
    Zend\Module\Consumer\AutoloaderProvider,
    Zend\Module\Consumer\LocatorRegistered,
    Zend\EventManager\EventDescription as Event;

abstract class ModuleAbstract implements AutoloaderProvider, LocatorRegistered
{
    abstract public function getDir();
    abstract public function getNamespace();
    
    public function init(Manager $moduleManager)
    {
        $events = StaticEventManager::getInstance();
        $instance = $this;//TODO this will no be needed in PHP 5.4
        $events->attach('bootstrap', 'bootstrap', function($e) use ($instance, $moduleManager) {
            $app = $e->getParam('application');
            $instance->bootstrap($moduleManager, $app);
        });
    }
    
    public function bootstrap(Manager $moduleManager, Application $app) {
        
    }
    
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                $this->getDir() . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    $this->getNamespace() => $this->getDir() . '/src/' . $this->getNamespace(),
                ),
            ),
        );
    }
    
    public function getConfig()
    {
        return include $this->getDir() . '/config/module.config.php';
    }
    
    /**
     * Returns module option value.
     * Dot character is used to separate sub arrays.
     * 
     * Example:
     * array(
     *      'option1' => 'this is my option 1'
     *      'option2' => array(
     *          'key1' => 'sub key1',
     *          'key2' => 'sub key2',
     *      )
     * )
     * 
     * $module->getOption('option1');
     * Returns: (string) "This is my option 1"
     *
     * $module->getOption('option2');
     * Returns: array(
     *          'key1' => 'sub key1',
     *          'key2' => 'sub key2',
     *      )
     * 
     * $module->getOption('option2.key1');
     * Returns: (string) "sub key1"
     * 
     * @param string $option
     * @param mixed $default
     * @return mixed 
     */
    public function getOption($option, $default = null) {
        $options = $this->getOptions();
        $optionArr = explode('.', $option);
        
        $option = $this->getOptionFromArray($options, $optionArr, $default, $option);
        return $option;
    }
    
    private function getOptionFromArray(array $options, array $option, $default, $origOption) {
        $currOption = array_shift($option);
        if(array_key_exists($currOption, $options)) {
            if(count($option) >= 1) {
                return $this->_getOption($options[$currOption], $option, $default, $origOption);
            }
            
            return $options[$currOption];
        }
        
        if($default !== null) {
            return $default;
        }
        
        throw new \InvalidArgumentException("Option '$origOption' is not set");
    }
    
    public function getOptions() {
        $config = $this->getConfig();
        if(empty($config[$this->getNamespace()]['options'])) {
            return array();
        }
        return $config[$this->getNamespace()]['options'];
    }
}
