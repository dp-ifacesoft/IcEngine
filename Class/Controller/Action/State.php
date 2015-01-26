<?php

/**
 * Состояние действия контроллера
 * 
 * @author morph
 */
class Controller_Action_State
{
    /**
     * Аргументы
     * 
     * @var array
     */
    protected $args;
    
    /**
     * Выходной транспорт контроллера
     * 
     * @var Data_Transport
     */
    protected $output;
    
    /**
     * Задача действия контроллера
     * 
     * @var Controller_Task
     */
    protected $task;
    
    /**
     * Конструктор
     * 
     * @param string $controller
     * @param string $action
     */
    public function __construct($controller, $action)
    {
        $this->task = new Controller_Task(array(
            'controller'    => $controller,
            'action'        => $action
        ));
    }
    
    /**
     * Приминить модификатор к состоянию действия 
     * 
     * @param Controller_Action_Modifier_Abstract $modifier
     */
    public function apply($modifier)
    {
        $modifier->run($this);
    }
    
    /**
     * Получить аргументы
     * 
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }
    
    /**
     * Получить текущий выходной транспорт
     * 
     * @return Data_Transport
     */
    public function getOutput()
    {
        return $this->output;
    }
    
    /**
     * Получить задание контроллера
     * 
     * @return Controller_Task
     */
    public function getTask()
    {
        return $this->task;
    }
    
    /**
     * Выполнить действие состояния
     */
    public function run()
    {
        $serviceLocator = IcEngine::serviceLocator();
        $controllerAction = $this->task->controllerAction();
        $controllerManager = $serviceLocator->getService('controllerManager');
        $controllerManager->call(
            $controllerAction['controller'], $controllerAction['action'],
            $this->getArgs()
        );
    }
    
    /**
     * Изменить аргументы
     * 
     * @param array $args
     */
    public function setArgs($args)
    {
        $this->args = $args;
    }
    
    /**
     * Изменить выходной транспорт
     * 
     * @param Data_Transport $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }
    
    /**
     * Изменить задание
     * 
     * @param Controller_Task $task
     */
    public function setTask($task)
    {
        $this->task = $task;
    }
}