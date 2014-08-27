<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат для редиректа после успешного завершения метода
 * 
 * @author morph
 */
class ControllerManagerDelegeeRedirect extends ControllerManagerDelegeeAbstract
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
        if (!empty($actionScheme['Redirect'])) {
            $redirect = reset($actionScheme['Redirect']);
            $redirectUrl = reset($redirect);
            if ($redirectUrl[0] == '$') {
                $redirectUrl = $context->getArgs()[substr($redirectUrl, 1)];
            }
            $slot = new \Event_Slot_Controller_Redirect();
            $slot->setParams(array(
                'redirect'    => $redirectUrl
            ));
            $controllerManager->getService('eventManager')->register(
                $signal, $slot
            ); 
        }
    }
}