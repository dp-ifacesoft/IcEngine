<?php
/**
 * Ошибка валидации сервиса REST API на поддержку текущего HTTP-метода
 */

class Validator_Error_Rest_Api_Allows_Http_Method extends Validator_Error_Rest_Api
{
    /**
     * @inheritdoc
     */
    protected $_httpStatus = 405;

    /**
     * @inheritdoc
     */
    public function errorCode()
    {
        return 'notAllowedHttpMethod';
    }

    /**
     * @inheritdoc
     */
    public function errorMessage($value = null)
    {
        return 'This HTTP method is not allowed in ' . $this->_getRestApiName() . '::' . $this->_getRestApiAction();
    }
} 