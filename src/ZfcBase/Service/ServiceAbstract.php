<?php

namespace ZfcBase\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface,
    Zend\ServiceManager\ServiceLocatorInterface,
    Zend\EventManager\EventManagerAwareInterface,
    Zend\EventManager\EventManagerInterface;

class ServiceAbstract implements ServiceLocatorAwareInterface, EventManagerAwareInterface {
    /**
     * @var EventManagerInterface
     */
    protected $events;
    
    /**
     * @var Zend\ServiceManager\ServiceManagerInterface;
     */
    protected $locator;
    
    public function setServiceLocator(ServiceLocatorInterface $locator) {
        $this->locator = $locator;
    }
    
    public function getServiceLocator() {
        return $this->locator;
    }
    
    /**
     * Set the event manager instance used by this context
     * 
     * @param  EventManagerInterface $events
     * @return mixed
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(__CLASS__, get_class($this)));
        $this->events = $events;
        $this->attachDefaultListeners();
        return $this;
    }

    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     * 
     * @return EventManagerInterface
     */
    public function events()
    {
        return $this->events;
    }
    
    /**
     * Method merges return values of each listener's response into original $argv array and returns it.
     * 
     * @param string $event
     * @param array $argv
     * @param callback $callback
     * @return array 
     */
    protected function triggerParamsMergeEvent($event, $argv = array(), $callback = null)
    {
        $eventRet = $this->triggerEvent($event, $argv, $callback);
        foreach($eventRet as $event) {
            if(is_array($event) || $event instanceof Traversable) {
                $argv = array_merge_recursive($argv, $event);
            }
        }
        
        return $argv;
    }
    
    protected function triggerEvent($event, $argv = array(), $callback = null)
    {
        return $this->events()->trigger($event, $this, $argv, $callback);
    }
    
    protected function attachDefaultListeners()
    {
        
    }
    
}
