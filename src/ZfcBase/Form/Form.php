<?php

namespace ZfcBase\Form;

use Zend\Form\Form as ZendForm,
    Zend\Form\SubForm;

class Form extends ProvidesEventsForm {
    
    public function __construct($options = null)
    {
        parent::__construct($options);
        
        $this->events()->trigger('construct.post', $this);
    }
    
    public function getExtSubForm($name = null) {
        $extsForm = $this->getSubForm('exts');
        if($extsForm === null) {
            $extsForm = new SubForm();
            $this->addSubForm($extsForm, 'exts');
        }
        
        if($name !== null) {
            return $extsForm->getSubForm($name);
        }
        
        return $extsForm;
    }
    
    public function addExtSubForm(ZendForm $form, $name) {
        $extsForm = $this->getExtSubForm();
        $form->setIsArray(true);
        $form->removeDecorator('FormDecorator');
        $extsForm->addSubForm($form, $name);
    }
    
}