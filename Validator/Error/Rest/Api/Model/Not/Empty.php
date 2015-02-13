<?php
/**
 * Ошибка валидации REST API сервиса на наличие установленного непустого имени текущей модели
 *
 * @author LiverEnemy
 */

class Validator_Error_Rest_Api_Model_Not_Empty extends Validator_Error_Rest_Api
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
        return 'modelIsEmpty';
    }

    public function errorMessage($value = null)
    {
        return $this->_getRestApiName() . ' property \'modelName\' is empty';
    }
} 