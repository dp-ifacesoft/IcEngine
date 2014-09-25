<?php
/**
 * Валидатор проверки REST API сервиса на поддержку текущего HTTP-метода
 *
 * @author LiverEnemy
 */

class Validator_Rest_Api_Allows_Http_Method extends Validator_Rest_Api
{
    /**
     * @inheritdoc
     */
    public function validate()
    {
        $restApi = $this->getData();
        $isOk = $restApi->allowsHttpMethod();
        if (!$isOk)
        {
            $error = $this->getValidatorError();
            $error->setParams([
                'Allow' => implode(',', $restApi->allowHttpMethods()),
            ]);
            $this->extendErrorParams();
        }
        return $isOk;
    }
} 