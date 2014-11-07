<?php

/**
 * Исключение "Ошибка 404" для валидаторов контроллера
 * 
 * @author morph
 */
class Controller_Validator_Exception_Not_Found extends 
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
            $task->setIgnore(true);
        } else {
            $this->setControllerAction($task, 'Error', 'notFound');
        }
    }
}