<?php

namespace IcEngine\Controller\Manager;

/**
 * Абстрактный делигат для менеджера контроллеров
 * 
 * @author morph
 */
abstract class ControllerManagerDelegeeAbstract
{
    /**
     * Выполнить делигата
     * 
     * @param Controller_Abstract $controller
     * @param Controller_Context $context
     */
    abstract public function call($controller, $context);
}