<?php

namespace ZfcBase\Service;

use Traversable;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;

class AbstractService implements
    ServiceLocatorAwareInterface,
    EventManagerAwareInterface
{
    /**
     * @var EventManagerInterface
     */
    protected $events;
    
    /**
     * @var ServiceLocatorInterface
     */
    protected $locator;

    /**
     * set service locator
     *
     * @param ServiceLocatorInterface $locator
     * @return AbstractService
     */
    public function setServiceLocator(ServiceLocatorInterface $locator)
    {
        $this->locator = $locator;
        return $this;
    }

    /**
     * get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->locator;
    }
    
    /**
     * Set the event manager instance used by this context
     * 
     * @param  EventManagerInterface $events
     * @return AbstractService
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(__CLASS__, get_called_class()));
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
            $this->setEventManager($this->getServiceLocator()->get('EventManager'));
        }
        return $this->events;
    }

    /**
     * attaches the default listeners for this service
     *
     * @return void
     */
    protected function attachDefaultListeners()
    {
    }
    
}
