<?php

/**
 * Исключение "Ошибка 403" для валидаторов контроллера
 * 
 * @author morph
 */
class Controller_Validator_Exception_Send_Error extends 
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
            $context->getController()->sendError($this->params['message']);
            $this->setControllerAction($task, 'Nope', 'nope');
        } 
    }
}