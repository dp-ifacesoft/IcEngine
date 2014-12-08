<?php

namespace IcEngine\Controller\Manager;

/**
 * Делегат, для получения значений из конфигурации и инъекции их 
 * в поля контроллера
 * 
 * @author morph
 */
class ControllerManagerDelegeeConfig extends ControllerManagerDelegeeAbstract
{
    /**
     * @inheritdoc 
     * @see IcEngine\Controller\Manager\ControllerManagerDelegeeAbstract::call
     */
    public function call($controller, $context)
    {
        $scheme = $controller->getAnnotations()->getData();
        $actionScheme = $scheme['properties'];
        $configManager = $controller->getService('configManager');
        foreach ($actionScheme as $property => $propertyData) {
            if (!isset($propertyData['Config'])) {
                continue;
            }
            $data = reset($propertyData['Config']);
            $propertyName = reset($data);
            $source = $data['source'];
            $config = $configManager->get($source);
            $propertyReflection = $context->getReflection()->getProperty(
                $property
            );
            $propertyReflection->setAccessible(true);
            $propertyReflection->setValue($controller, $config[$propertyName]);
        }
    }
}