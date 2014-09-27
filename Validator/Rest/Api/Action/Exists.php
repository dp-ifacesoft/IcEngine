<?php
/**
 * Валидатор проверки сервиса REST API на существование метода, указанного в элементе action массива requestData
 *
 * @author LiverEnemy
 */

class Validator_Rest_Api_Action_Exists extends Validator_Rest_Api
{
    /**
     * @inheritdoc
     */
    public function validate()
    {
        $restApi = $this->getData();
        $action = $restApi->getAction();
        $isOk = !empty($action) && method_exists($restApi, $action);
        if (!$isOk)
        {
            $this->extendErrorParams();
        }
        return $isOk;
    }
} 