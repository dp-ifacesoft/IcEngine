<?php

/**
 * Спецификация для определения post/ajax запросов
 * 
 * @author morph
 */
class Controller_Front_Strategy_Specification_Post extends
    Controller_Front_Strategy_Specification_Abstract
{
    /**
     * @inheritdoc
     */
    public function isSatisfiedBy($task)
    {
        $request = $task->getService('request');
        return $request->isPost() || $request->isAjax();
    }
}