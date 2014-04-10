<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат для определения доступно ли действие контроллера для текущей роли
 * 
 * @author morph
 */
class ControllerManagerDelegeeRole extends ControllerManagerDelegeeAbstract
{
    /**
     * @inheritdoc 
     * @see IcEngine\Controller\Manager\ControllerManagerDelegeeAbstract::call
     */
    public function call($controller, $context)
    {
        $controllerManager = $context->getControllerManager();
        $scheme = $controllerManager->annotationManager()
            ->getAnnotation($controller);
        $user = $controller->getService('user')->getCurrent();
        $actionScheme = $scheme->getMethod($context->getAction());
        $request = $controller->getService('request');
        if (!empty($actionScheme['Role'])) {
            $roles = array();
            foreach ($actionScheme['Role'][0] as $role) {
                $roles[] = $role;
            }
            if (!$user->hasRole($roles)) {
                if ($request->isAjax() || $request->isPost()) {
                    $controller->getTask()->setIgnore(true);
                } else {
                    $newController = $controllerManager->get('Error');
                    $newController
                        ->setTask($controller->getTask())
                        ->setInput($controller->getInput())
                        ->setOutput($controller->getOutput());
                    $controller->getTask()->setCallable(
                        $newController, 'accessDenied'
                    );
                }
            }
        }
    }
}