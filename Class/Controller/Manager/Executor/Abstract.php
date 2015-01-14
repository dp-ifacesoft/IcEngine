<?php

/**
 * Абстрактный "выполнитель" действий контроллера
 * 
 * @author morph
 */
abstract class Controller_Manager_Executor_Abstract
{
    /**
     * Выполнить действие
     * 
     * @param array $callable
     * @param array $args
     */
    abstract public function execute($callable, $args);
}