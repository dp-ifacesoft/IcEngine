<?php

/**
 * Абстрактная команда api
 *
 * @author markov
 */
abstract class Api_Command_Abstract
{
    /**
     * Параметры
     */
    protected $params; 
    
    /**
     * @return mixed результат выполнения команды
     */
    abstract public function run();
    
    /**
     * Устанавливает параметры
     */
    public function setParams($params)
    {
        $this->params = $params;
    }
}