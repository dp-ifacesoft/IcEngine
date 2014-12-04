<?php
/**
 * Валидатор сервиса REST API на наличие элемента ID в поле $_requestData
 *
 * @author LiverEnemy
 */

class Validator_Rest_Api_Request_Data_Has_Id extends Validator_Rest_Api
{
    /**
     * @inheritdoc
     */
    public function validate()
    {
        $restApi = $this->getData();
        $requestData = $restApi->getRequestData();
        $isOk = !empty($requestData['id']);
        if (!$isOk)
        {
            $this->extendErrorParams();
        }
        return $isOk;
    }
} 