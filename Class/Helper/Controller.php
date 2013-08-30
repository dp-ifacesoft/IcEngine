<?php

/**
 * Хелпер для работы с контроллерами
 * 
 * @author morph
 * @Service("helperController")
 */
class Helper_Controller
{
    /**
     * Получить имя шаблона
     * 
     * @param Controller_Abstract $controller
     * @param string $action
     */
    public function getTemplate($controller, $action)
    {
        $template = 'Controller/' . str_replace('_', '/', $controller->name()) .
            '/' . $action;
        return $template;
    }
    
    /**
     * Внедрение аргументов в метод контроллера
     * 
     * @param Controller_Abstract $controller
     * @param string $action
     */
    public function invokeArgs($controller, $action)
    {
        $reflection = new \ReflectionMethod($controller, $action);
        $params = $reflection->getParameters();
        $currentInput = $controller->getInput();
        $provider = $currentInput->getProvider(0);
        $resultParams = array();
        if ($params) {
            foreach ($params as $param) {
                $value = $currentInput->receive($param->name);
                if (!$value && $param->isOptional()) {
                    $value = $param->getDefaultValue();
                }
                if ($provider) {
                    $provider->set($param->name, $value);
                }
                $resultParams[$param->name] = $value;
            }
        }
        $reflection->invokeArgs($controller, $resultParams);
    }
}