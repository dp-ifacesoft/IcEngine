<?php

/**
 * Абстрактная стратегия front-контроллера
 * 
 * @author morph
 */
abstract class Controller_Front_Strategy_Abstract
{
    /**
     * Будет ли игнорироваться диспетчеризация
     *
     * @var boolean
     */
    protected $ignore;
    
    /**
     * Входящий транспорт
     * 
     * @var Data_Transport
     */
    protected $input;
    
    /**
     * Выходящий транспорт
     * 
     * @var Data_Transport
     */
    protected $output;
    
    /**
     * Спецификация стратегии
     * 
     * @var Controller_Front_Strategy_Specification_Abstract
     */
    protected $specification;
    
    /**
     * Задача front-контроллера
     *  
     * @var Controller_Front_Task
     */
    protected $task;
    
    /**
     * Шаблон для фронт контроллера
     * 
     * @var string
     */
    protected $template;
    
    /**
     * Выполнить стратегию
     */
    abstract public function action();
    
    /**
     * Получить игнорирование
     * 
     * @return boolean
     */
    public function getIgnore()
    {
        return $this->ignore;
    }
    
    /**
     * Получить входной транспорт
     * 
     * @return Data_Transport
     */
    public function getInput()
    {
        return $this->input;
    }
    
    /**
     * Получить имя стратегии
     * 
     * @return string
     */
    public function getName()
    {
        return substr(get_class($this), strlen('Controller_Front_Strategy_'));
    }
    
    /**
     * Получить выходной транспорт
     * 
     * @return Data_Transport
     */
    public function getOutput()
    {
        return $this->output;
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
     * Получить спецификацию
     * 
     * @return Controller_Front_Strategy_Specification_Abstract
     */
    public function getSpecification()
    {
        if (is_null($this->specification)) {
            $className = 'Controller_Front_Strategy_Specification_' .
                $this->getName();
            $this->specification = new $className;
        }
        return $this->specification;
    }
    
    /**
     * Получить задание
     * 
     * @return Controller_Front_Task
     */
    public function getTask()
    {
        return $this->task;
    }
    
    /**
     * Получить шаблон
     * 
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }
    
    /**
     * Удовлетворяет ли спецификация стратегии окружению
     * 
     * @param Controller_Front_Task $task
     * @return boolean
     */
    public function isSatisfiedBy($task)
    {
        return $this->getSpecification()->isSatisfiedBy($task);
    }
    
    /**
     * Задать окружение и выполнить стратегию
     * 
     * @param Controller_Front_Task $task
     */
    public function run($task)
    {
        $this->task = $task;
        $this->input = $task->getInput();
        $this->output = $task->getOutput();
        if ($this->template) {
            $task->setTemplate($this->template);
        }
        $this->action();
    }
    
    /**
     * Изменить игнорирование
     * 
     * @param boolean $ignore
     */
    public function setIgnore($ignore)
    {
        $this->ignore = $ignore;
    }
    
    /**
     * Изменить входной транспорт
     * 
     * @param Data_Transport $input
     */
    public function setInput($input)
    {
        $this->input = $input;
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
     * Изменить спецификацию
     * 
     * @param Controller_Front_Strategy_Specification_Abstract $specification
     */
    public function setSpecification($specification)
    {
        $this->specification = $specification;
    }
    
    /**
     * Изменить задание
     * 
     * @param Controller_Front_Task $task
     */
    public function setTask($task)
    {
        $this->task = $task;
    }
    
    /**
     * Изменить шаблон
     * 
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }
}