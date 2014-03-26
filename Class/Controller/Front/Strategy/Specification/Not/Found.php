<?php

/**
 * Спецификация для 404
 * 
 * @author morph
 */
class Controller_Front_Strategy_Specification_Not_Found extends 
    Controller_Front_Strategy_Specification_Abstract
{
    /**
     * @inheritdoc
     */
    public function isSatisfiedBy($task)
    {
        return !$task->getRoute();
    }
}