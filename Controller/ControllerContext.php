<?php

namespace IcEngine\Controller;

/**
 * Контекст котроллера
 * 
 * @author morph
 */
class ControllerContext
{
    /**
     * Действие контроллера
     * 
     * @var string
     */
    protected $action;
    
    /**
     * Аргументы, которые будут переданы в входной транспорт контроллера
     * 
     * @var array
     */
    protected $args = array();
    
    /**
     * Контроллер, который будет вызван в контексте
     * 
     * @var Controller_Abstract
     */
    protected $controller;
    
    /**
     * Менеджер контроллеров
     * 
     * @var Controller_Manager
     */
    protected $controllerManager;
    
    /**
     * Рефлексия контроллера
     * 
     * @var \ReflectionClass
     */
    protected $reflection;
    
    /**
     * Получить действие контроллера
     * 
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }
    
    /**
     * Получить аргументы для входного транспорта контроллера
     * 
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }
    
    /**
     * Получить контроллер
     * 
     * @return Controller_Abstract
     */
    public function getController()
    {
        return $this->controller;
    }
    
    /**
     * Получить менеджер контроллеров
     * 
     * @return Controller_Manager
     */
    public function getControllerManager()
    {
        return $this->controllerManager;
    }
    
    /**
     * Получить рефлексию контроллера
     * 
     * @return \ReflectionClass
     */
    public function getReflection()
    {
        if (!$this->reflection) {
            $this->reflection = new \ReflectionClass($this->controller);
        }
        return $this->reflection;
    }
    
    /**
     * Изменить действие контроллера
     * 
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }
    
    /**
     * Изменить входные параметры контроллера
     * 
     * @param array $args
     */
    public function setArgs($args)
    {
        $this->args = $args;
    }
    
    /**
     * Изменить контроллер
     * 
     * @param Controller_Abstract $controller
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }
    
    /**
     * Изменить менеджер контроллера
     * 
     * @param Controller_Manager $controllerManager
     */
    public function setControllerManager($controllerManager)
    {
        $this->controllerManager = $controllerManager;
    }
    
    /**
     * Изменить рефлексию контроллера
     * 
     * @param \ReflectionClass $reflection
     */
    public function setReflection($reflection)
    {
        $this->reflection = $reflection;
    }
}