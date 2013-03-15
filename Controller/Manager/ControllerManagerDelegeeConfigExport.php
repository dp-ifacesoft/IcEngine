<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат для экспорта значений из конфига и передачи его во входящий 
 * транспорт
 * 
 * @author morph
 */
class ControllerManagerDelegeeConfigExport extends 
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
        if (!empty($actionScheme['ConfigExport'])) {
            $config = $controller->config();
            $params = $context->getArgs();
            foreach ($actionScheme['ConfigExport'] as $data) {
                $configField = reset($data);
                $toField = isset($data['to']) ? $data['to'] : $configField;
                $params[$toField] = $config[$configField];
            }
            $context->setArgs($params);
        }
    }
}