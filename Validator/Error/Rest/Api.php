<?php
/**
 * Абстрактная ошибка валидации сервиса REST API
 *
 * @author LiverEnemy
 */

abstract class Validator_Error_Rest_Api extends Validator_Error
{
    /**
     * @var integer Соответствующий ошибке HTTP-статус
     */
    protected $_httpStatus;

    /**
     * Получить соответствующий ошибке HTTP-статус
     *
     * @return int
     */
    protected function _getHttpStatus()
    {
        return $this->_httpStatus;
    }

    /**
     * Получить название текущего метода REST API
     *
     * @return string
     */
    protected function _getRestApiAction()
    {
        $params = $this->getParams();
        $action = !empty($params['action']) ? $params['action'] : 'action';
        return $action;
    }

    /**
     * Получить название класса проверяемого сервиса REST API
     *
     * @return string
     */
    protected function _getRestApiName()
    {
        $params = $this->getParams();
        $name = !empty($params['service']) ? $params['service'] : 'REST API service';
        return $name;
    }
    /**
     * Обработать ошибку валидации
     *
     * @return $this
     * @throws Exception В случае, если разработчик валидатора забыл указать $_httpStatus, который надо отправить
     */
    public function processError()
    {
        $httpStatus = $this->_getHttpStatus();
        if (empty($httpStatus))
        {
            throw new Exception(__METHOD__ . ' requires an _httpStatus property of ' . get_class($this). ' to be set');
        }
        $params = $this->getParams();
        /** @var Service_Http_Header $serviceHttpHeader */
        $serviceHttpHeader = $this->getService('serviceHttpHeader');
        $serviceHttpHeader->sendHeaderHttpStatus($httpStatus, true, $params);
        return $this;
    }
} 