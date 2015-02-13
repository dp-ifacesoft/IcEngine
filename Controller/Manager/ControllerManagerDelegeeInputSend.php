<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат для отправки данных в входной транспорт контроллера
 * 
 * @author morph
 */
class ControllerManagerDelegeeInputSend extends ControllerManagerDelegeeAbstract
{
    /**
     * @inheritdoc 
     * @see IcEngine\Controller\Manager\ControllerManagerDelegeeAbstract::call
     */
    public function call($controller, $context)
    {
        $scheme = $controller->getAnnotations();
        $actionScheme = $scheme->getMethod($context->getAction());
        if (!empty($actionScheme['InputSend'])) {
            $input = $controller->getInput();
            foreach ($actionScheme['InputSend'] as $data) {
                foreach ($data as $vars) {
                    $input->send($vars);
                }
            }
        }
    }
}