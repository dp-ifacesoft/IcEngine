<?php

/**
 * Простой "выполнить" действий контроллера
 */
class Controller_Manager_Executor_Simple extends 
    Controller_Manager_Executor_Abstract
{
    /**
     * @inheritdoc
     */
    public function execute($callable, $args)
    {
        call_user_func_array($callable, $args);
    }
}