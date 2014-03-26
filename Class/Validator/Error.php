<?php


/**
 * Ошибки валидации
 *
 * @author markov
 */
class Validator_Error 
{ 
    /**
     * Параметры
     */
    protected $params = array();
    
    /**
     * Получить параметры
     * 
     * @return array $params
     */
    public function getParams() 
    {
        return $this->params;
    }
    
    /**
     * Устанавливает параметры
     * 
     * @param array $params
     * @return $this
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }    
    
    /**
     * Возвращает код ошибки
     *
     * @return string
     */
    public function errorCode()
    {
        return 'errorCode';
    }

    /**
     * Возвращает текст ошибки
     * 
     * @param mixed $value
     * @return string
     */
    public function errorMessage($value = null)
    {
        return 'Ошибка валидации';
    }

    /**
     * Получить экземпляр сервиса
     *
     * @param string $name
     * @return mixed
     */
    protected function getService($name)
    {
        return IcEngine::serviceLocator()->getService($name);
    }
}
