<?php
/**
 * Ошибка валидации сервиса REST API на наличие элемента ID в поле $_requestData
 *
 * @author LiverEnemy
 */

class Validator_Error_Rest_Api_Request_Data_Has_Id extends Validator_Error_Rest_Api
{
    /**
     * @inheritdoc
     */
    protected $_httpStatus = 406;

    /**
     * @inheritdoc
     */
    public function errorCode()
    {
        return 'hasNotId';
    }

    public function errorMessage($value = null)
    {
        return $this->_getRestApiName() . '::' . $this->_getRestApiAction()
            . ' requires an ID attribute of requestData to be set and not empty';
    }
} 