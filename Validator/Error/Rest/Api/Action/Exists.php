<?php
/**
 * Ошибка проверки сервиса REST API на существование метода, указанного в элементе action его массива requestData
 *
 * @author LiverEnemy
 */

class Validator_Error_Rest_Api_Action_Exists extends Validator_Error_Rest_Api
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
        return 'actionNotExists';
    }

    public function errorMessage($value = null)
    {
        return 'Requested action ' . $this->_getRestApiName() . '::' . $this->_getRestApiAction()
            . ' does not exist';
    }
} 