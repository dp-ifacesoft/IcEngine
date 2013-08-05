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
            $input = $controller->getInput();
            foreach ($actionScheme['ConfigExport'] as $data) {
                foreach ($data as $configField) {
                    if (is_array($configField)) {
                        $toField = isset($configField['to']) 
                            ? $configField['to'] : reset($configField);
                        $configField = reset($configField);
                    } else {
                        $toField = $configField;
                    }
                    if (empty($input[$toField])) {
                        $input[$toField] = $config[$configField];
                    }
                }
            }
        }
    }
}