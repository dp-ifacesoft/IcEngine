<?php

/**
 * Абстрактная спецификация стратегии front-контроллера
 * 
 * @author morph
 */
abstract class Controller_Front_Strategy_Specification_Abstract
{
    /**
     * Удовлетворяет ли спецификация требованиям
     * 
     * @param Controller_Font_Task $task
     * @return boolen
     */
    abstract public function isSatisfiedBy($task);
}