<?php
/**
 * Ошибка валидации сервиса REST API на корректность целевого метода
 *
 * @author LiverEnemy
 */

class Validator_Error_Rest_Api_Action_Correct extends Validator_Error_Rest_Api
{
    /**
     * @inheritdoc
     */
    protected $_httpStatus = 403;

    /**
     * @inheritdoc
     */
    public function errorCode()
    {
        return 'actionIsNotCorrect';
    }

    public function errorMessage($value = null)
    {
        return 'Call prohibited action ' . $this->_getRestApiName() . '::' . $this->_getRestApiAction();
    }
} 