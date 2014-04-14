<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат для объединения конфигов
 * транспорт
 * 
 * @author morph
 */
class ControllerManagerDelegeeConfigMerge extends 
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
        if (!empty($actionScheme['ConfigMerge'])) {
            $configManager = $controllerManager->getService('configManager');
            $config = $controller->config();
            foreach ($actionScheme['ConfigMerge'] as $data) {
                foreach ($data as $subData) {
                    $subConfig = $configManager->get($subData);
                    if (!$subConfig) {
                        continue;
                    }
                    $config->merge($subConfig);
                }
            }
        }
    }
}