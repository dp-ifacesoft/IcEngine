<?php

/**
 * Стратегия для 404
 * 
 * @author morph
 */
class Controller_Front_Strategy_Not_Found extends 
    Controller_Front_Strategy_Abstract
{ 
    /**
     * @inheritdoc
     */
    public function action()
    {
        $this->output->send(array(
            'domain'    => $this->getService('request')->host()
        ));
        $this->task->setActions(array('Error/notFound'));
    }
}