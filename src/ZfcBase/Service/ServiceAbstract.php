<?php

namespace ZfcBase\Service;

use Zend\Loader\LocatorAware,
    Zend\Di\Locator,
    Zend\EventManager\EventCollection,
    Zend\EventManager\EventManager;


class ServiceAbstract implements LocatorAware {
    /**
     * @var Zend\Loader\Locator;
     */
    protected $locator;
    
    /**
     * Method merges return values of each listener's response into original $argv array and returns it.
     * 
     * @param string $event
     * @param array $argv
     * @param callback $callback
     * @return array 
     */
    protected function triggerParamsMergeEvent($event, $argv = array(), $callback = null) {
        $eventRet = $this->triggerEvent($event, $argv, $callback);
        foreach($eventRet as $event) {
            if(is_array($event) || $event instanceof Traversable) {
                $argv = array_merge_recursive($argv, $event);
            }
        }
        
        return $argv;
    }
    
    protected function triggerEvent($event, $argv = array(), $callback = null) {
        return $this->events()->trigger($event, $this, $argv, $callback);
    }
    
    public function setLocator(Locator $locator) {
        $this->locator = $locator;
    }
    
    public function getLocator() {
        return $this->locator;
    }
    
    //--- Zend/EventManager/ProvidesEvents trait - let's wait for 5.4
    /**
     * @var EventCollection
     */
    protected $events;

    /**
     * Set the event manager instance used by this context
     * 
     * @param  EventCollection $events 
     * @return mixed
     */
    public function setEventManager(EventCollection $events)
    {
        $this->events = $events;
        return $this;
    }

    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     * 
     * @return EventCollection
     */
    public function events()
    {
        if (!$this->events instanceof EventCollection) {
            $identifiers = array(__CLASS__, get_class($this));
            if (isset($this->eventIdentifier)) {
                if ((is_string($this->eventIdentifier))
                    || (is_array($this->eventIdentifier))
                    || ($this->eventIdentifier instanceof Traversable)
                ) {
                    $identifiers = array_unique(array_merge($identifiers, (array) $this->eventIdentifier));
                } elseif (is_object($this->eventIdentifier)) {
                    $identifiers[] = $this->eventIdentifier;
                }
                // silently ignore invalid eventIdentifier types
            }
            $this->setEventManager(new EventManager($identifiers));
            $this->attachDefaultListeners();
        }
        return $this->events;
    }
    
    protected function attachDefaultListeners() {
        
    }
    
    //--- END trait
    
}