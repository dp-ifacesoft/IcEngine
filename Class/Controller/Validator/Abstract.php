<?php

/**
 * Абстрактный валидатор контроллеров
 * 
 * @author morph
 */
abstract class Controller_Validator_Abstract
{
    /**
     * Контекст выполняющегося контроллера
     * 
     * @var ControllerContext
     */
    protected $context;
    
    /**
     * Конструктор
     * 
     * @param ControllerContext $context
     */
    public function __construct($context)
    {
        $this->context = $context;
    }
    
    /**
     * Бросить исключение "Access_Denied"
     */
    public function accessDenied()
    {
        return $this->throwException('Access_Denied', array());
    }
    
    /**
     * Получить контекст контроллера
     * 
     * @return ControllerContext
     */
    public function getContext()
    {
        return $this->context;
    }
    
    /**
     * Получить сервис по имени
     * 
     * @param string $serviceName
     * @return mixed
     */
    public function getService($serviceName)
    {
        return IcEngine::serviceLocator()->getService($serviceName);
    }
    
    /**
     * Бросить исклчючение "Not_Found"
     */
    public function notFound()
    {
        return $this->throwException('Not_Found', array());
    }
    
    /**
     * Бросить исключение "Redirect"
     * 
     * @param string $url
     */
    public function redirect($url)
    {
        return $this->throwException(
            'Send_Error', array('url' => $url)
        );
    }
    
    /**
     * Бросить исключение "Send_Error"
     * 
     * @param string $message
     */
    public function sendError($message)
    {
        return $this->throwException(
            'Send_Error', array('message' => $message)
        );
    }
    
    /**
     * Изменить контекс контроллера
     * 
     * @param ControllerContext $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * Породить исключение валидатора контроллеров по имени
     *
     * @param string $name
     * @param $params
     * @return bool
     */
    protected function throwException($name, $params)
    {
        $exceptionManager = $this->getService(
            'controllerValidatorExceptionManager'
        );
        $params = array_merge(array('context' => $this->context), $params);
        try {
            $exceptionManager->get($name, $params);
        } catch(Controller_Validator_Exception_Abstract $e) {
            return false;
        }
        return true;
    }
    
    /**
     * Выполнить валидацию
     * 
     * @param array $params
     */
    abstract public function validate($params);
}