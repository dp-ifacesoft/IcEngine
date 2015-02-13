<?php
/**
 * Ошибка валидации сервиса REST API на наличие непустого массива полей модели, с которыми сервису разрешено работать
 *
 * @author LiverEnemy
 */

class Validator_Error_Rest_Api_Model_Fields_Not_Empty extends Validator_Error_Rest_Api
{
    /**
     * @inheritdoc
     */
    protected $_httpStatus = 500;

    /**
     * @inheritdoc
     */
    public function errorCode()
    {
        return 'emptyModelFields';
    }

    /**
     * @inheritdoc
     */
    public function errorMessage($value = null)
    {
        return $this->_getRestApiName() . ' property \'modelFields\' is empty';
    }
} 