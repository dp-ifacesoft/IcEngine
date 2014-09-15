<?php

/**
 * Абстрактный модификатор состояния действия контроллера
 * 
 * @author morph
 */
abstract class Controller_Action_Modifier_Abstract
{
    /**
     * Аргументы
     * 
     * @var array 
     */
    protected $args;
    
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
     * Приминяет модификатор к состоянию
     * 
     * @param Controller_Action_State $state
     */
    abstract public function run($state);
    
    /**
     * Изменить аргументы
     * 
     * @param array $args
     */
    public function setArgs($args)
    {
        $this->args = $args;
    }
}