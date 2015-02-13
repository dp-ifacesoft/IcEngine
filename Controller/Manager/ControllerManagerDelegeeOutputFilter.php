<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат для установки фильтра выхода
 * 
 * @author morph
 */
class ControllerManagerDelegeeOutputFilter extends 
    ControllerManagerDelegeeAbstract
{ 
    /**
     * @inheritdoc 
     * @see IcEngine\Controller\Manager\ControllerManagerDelegeeAbstract::call
     */
    public function call($controller, $context)
    {
        $scheme = $controller->getAnnotations();
        $actionScheme = $scheme->getMethod($context->getAction());
        $controllerManager = $context->getControllerManager();
        $key = $controller->getName() . '/' . $context->getAction();
        $signal = new \Event_Signal(
            array(
                'controller'    => $controller,
                'context'       => $context
            ),
            $key
        );
        if (!empty($actionScheme['OutputFilter'])) {
            $filter = reset($actionScheme['OutputFilter']);
            $filterName = reset($filter);
            if ($filterName[0] == '$') {
                $filterName = $context->getArgs()[substr($filterName, 1)];
            }
            $slot = new \Event_Slot_Controller_OutputFilter();
            $slot->setParams(array(
                'filterName'    => $filterName
            ));
            $controllerManager->getService('eventManager')->register(
                $signal, $slot
            ); 
        }
    }
}