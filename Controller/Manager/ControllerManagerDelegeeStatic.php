<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат для добавления к действию контроллера статических файлов
 *
 * @author morph
 */
class ControllerManagerDelegeeStatic extends ControllerManagerDelegeeAbstract
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
        $actionScheme = $scheme->getMethod($context->getAction());
        if (!empty($actionScheme['Static'])) {
            $helperViewResource = $controller->getService('helperViewResource');
            foreach ($actionScheme['Static'] as $static) {
                if (empty($static['file'])) {
                    continue;
                }
                $files = $static['file'];
                if (!is_array($files)) {
                    $files = array($files => $files);
                }
                if (isset($static['dir'])) {
                    $dir = ltrim($static['dir'], DIRECTORY_SEPARATOR) .
                        DIRECTORY_SEPARATOR;
                    foreach ($files as $index => $file) {
                        $files[$index] = $dir . $file;
                    }
                }
                if (isset($static['type'])) {
                    $type = $static['type'];
                } else {
                    $type = reset($static);
                }
                $params = array();
                if (isset($static['params'])) {
                    $params = $static['params'];
                }
                $group = !empty($static['group']) ? $static['group'] : 'default';
                foreach ($files as $file) {
                    $helperViewResource->append(
                        $type, array($file, $group, $params)
                    );
                }
            }
        }
    }
}