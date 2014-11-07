<?php

/**
 * Редирект на указанный урл
 * 
 * @author morph
 */
class Controller_Validator_Exception_Redirect extends 
    Controller_Validator_Exception_Abstract
{
    /**
     * @inheritdoc
     */
    public function buildMessage()
    {
        $request = $this->getService('request');
        $context = $this->params['context'];
        $task = $context->getController()->getTask();
        if ($request->isAjax()) {
            $this->getService('helperHeader')->redirect($this->params['url']);
            $this->setControllerAction($task, 'Nope', 'nope');
        } 
    }
}