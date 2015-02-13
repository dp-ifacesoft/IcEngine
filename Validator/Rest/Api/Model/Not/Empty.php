<?php
/**
 * Валидатор проверки REST API сервиса на наличие установленного поля modelName
 *
 * @author LiverEnemy
 */

class Validator_Rest_Api_Model_Not_Empty extends Validator_Rest_Api
{
    /**
     * @inheritdoc
     */
    public function validate()
    {
        $restApi = $this->getData();
        $modelName = $restApi->getModelName();
        $isOk = !empty($modelName);
        if (!$isOk)
        {
            $this->extendErrorParams();
        }
        return $isOk;
    }
} 