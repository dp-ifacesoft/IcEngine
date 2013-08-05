<?php

/**
 * Контрллер для запуска сервиса
 *
 * @author neon
 */
class Controller_Service extends Controller_Abstract
{
    /**
     * Запуск
     * 
     * @Validator("User_Cli")
     */
    public function run($name, $method)
    {
        $this->task->setTemplate(null);
        $service = $this->getService($name);
        if (!$service) {
            return $this->replaceAction('Error', 'notFound');
        }
        call_user_func_array(array($service, $method), array());
    }
}