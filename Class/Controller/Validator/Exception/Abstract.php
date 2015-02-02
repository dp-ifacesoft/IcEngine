<?php

/**
 * Абстрактное исключение валидатора контроллеров
 * 
 * @author morph
 */
abstract class Controller_Validator_Exception_Abstract extends Exception
{
    /**
     * Аргументы
     * 
     * @param array
     */
    protected $params;
    
    /**
     * @inheritdoc
     */
    public function __construct($params, $code = 0)
    {
        $this->params = $params;
        parent::__construct($this->buildMessage(), $code);
    }
    
    /**
     * Сформировать строку ошибки, или выполнить действие в связи с ошибкой
     * 
     * @return string
     */
    abstract public function buildMessage();
    
    /**
     * Получить аргменты
     * 
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
    
    /**
     * Получить сервис по имени
     * 
     * @param string $serviceName
     * @return mixed
     */
    public function getService($serviceName)
    {
        return IcEngine::serviceLocator()->getService($serviceName);
    }
    
    /**
     * Изменить подготовленные контроллер и действие для задания
     * 
     * @param Controller_Task $task
     * @param string $controllerName
     * @param string $actionName
     */
    public function setControllerAction($task, $controllerName, $actionName)
    {
        $controllerManager = $this->getService('controllerManager');
        $controller = $controllerManager->get($controllerName);
        $currentController = $task->getCallable()[0];
        $controller
            ->setInput($currentController->getInput())
            ->setOutput($currentController->getOutput())
            ->setTask($task);
        $task->setCallable($controller, $actionName);
    }
    
    /**
     * Изменить аргументы
     * 
     * @param array $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }
}