<?php

/**
 * Работа с тэгами контроллера
 * 
 * @author morph
 */
class Controller_Controller_Tag extends Controller_Abstract
{
    /**
     * @Template(null)
     * @Context("controllerTagManager")
     */
    public function run($tag, $context)
    {
        $params = $this->input->receiveAll();
        $controllerActions = $context->controllerTagManager->getFor($tag);
        foreach ($controllerActions as $controllerAction) {
            echo 'Run ' . $controllerAction . PHP_EOL;
            list($controller, $action) = explode('/', $controllerAction);
            $context->controllerManager->call($controller, $action, $params);
        }
    }
}