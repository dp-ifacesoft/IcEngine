<?php

/**
 * Абстрактная модель ссылки схемы связей модели
 * 
 * @author morph
 * @package Ice\Orm
 * @ServiceAccessor
 */
abstract class Model_Mapper_Reference_Abstract
{
    /**
     * Аргументы связи
     * 
     * @var array
     * @Generator
     */
    protected $args;
    
    /**
     * Модель
     * 
     * @var Model
     * @Generator
     */
	protected $model;
    
    /**
     * Поле, в которое будет создана связь
     * 
     * @var string
     * @Generator
     */
    protected $field;
    
    /**
     * Выполнить необходимые действия для создания связи
     * 
     * @return mixed
     */
    abstract public function execute();
    
    /**
     * Getter for "args"
     *
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }
        
    /**
     * Setter for "args"
     *
     * @param array args
     */
    public function setArgs($args)
    {
        $this->args = $args;
    }
    
    
    /**
     * Getter for "model"
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }
        
    /**
     * Setter for "model"
     *
     * @param Model model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }
    
    
    /**
     * Getter for "field"
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }
        
    /**
     * Setter for "field"
     *
     * @param string field
     */
    public function setField($field)
    {
        $this->field = $field;
    }
    
    /**
     * Get service by name
     *
     * @param string $serviceName
     * @return mixed
     */
    public function getService($serviceName)
    {
        return IcEngine::serviceLocator()->getService($serviceName);
    }
}