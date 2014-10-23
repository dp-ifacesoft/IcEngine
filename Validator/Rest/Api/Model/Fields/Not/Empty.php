<?php
/**
 * Валидатор проверки сервиса REST API на разрешение работать хоть с какими-то полями его модели
 *
 * @author LiverEnemy
 */

class Validator_Rest_Api_Model_Fields_Not_Empty extends Validator_Rest_Api
{
    /**
     * @inheritdoc
     */
    public function validate()
    {
        $restApi = $this->getData();
        $modelFields = $restApi->getModelFields();
        $isOk = !empty($modelFields);
        if (!$isOk)
        {
            $this->extendErrorParams();
        }
        return $isOk;
    }
} 