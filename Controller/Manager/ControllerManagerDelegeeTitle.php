<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат для задания заголовка
 * 
 * @author morph
 */
class ControllerManagerDelegeeTitle extends ControllerManagerDelegeeAbstract
{ 
    /**
     * @inheritdoc 
     * @see IcEngine\Controller\Manager\ControllerManagerDelegeeAbstract::call
     */
    public function call($controller, $context)
    {
        $scheme = $controller->getAnnotations();
        $actionScheme = $scheme->getMethod($context->getAction());
        if (empty($actionScheme['Title'])) {
            return;
        }
        $controllerManager = $context->getControllerManager();
        $key = $controller->getName() . '/' . $context->getAction();
        $eventManager = $controllerManager->getService('eventManager');
        $signal = $eventManager->getSignal($key);
        $slot = $eventManager->getSlot('Title');
        $titles = array();
        foreach ($actionScheme['Title'] as $data) {
            $params = array_values($data);
            $specification = $params[0];
            $dataTitles = array_values($params[1]);
            $pageTitle = $dataTitles[0];
            $siteTitle = isset($dataTitles[1]) ? $dataTitles[1] : $pageTitle;
            $titles[$specification] = array($pageTitle, $siteTitle);
        }
        $slot->setParams(array(
            'titles'    => $titles,
            'context'   => $context
        ));
        $eventManager->register($signal, $slot);
    }
}