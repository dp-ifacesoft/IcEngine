<?php

/**
 * Спецификация для определения ajax запросов
 * 
 * @author markov
 */
class Controller_Front_Strategy_Specification_Ajax extends
    Controller_Front_Strategy_Specification_Abstract
{
    /**
     * @inheritdoc
     */
    public function isSatisfiedBy($task)
    {
        $request = $task->getService('request');
        return $request->isAjax();
    }
}