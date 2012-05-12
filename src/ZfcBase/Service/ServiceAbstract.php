<?php

namespace ZfcBase\Service;

use Zend\Loader\LocatorAware,
    Zend\EventManager\EventManagerAwareInterface,
    Zend\Di\LocatorInterface,
    Zend\EventManager\EventManagerInterface;

class ServiceAbstract implements LocatorAware, EventManagerAwareInterface {
    /**
     * @var EventManagerInterface
     */
    protected $events;
    
    /**
     * @var Zend\Loader\Locator;
     */
    protected $locator;
    
    public function setLocator(LocatorInterface $locator) {
        $this->locator = $locator;
    }
    
    public function getLocator() {
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
        if (!$this->events instanceof EventManagerInterface) {
            $this->setEventManager(new EventManager());
        }
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