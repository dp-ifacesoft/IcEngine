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
        /** @var Request $request */
        $request = $this->getService('request');
        $httpMethod = $request->requestMethod();
        $restApi = $this->getData();
        $isOk = $restApi->allowsMethod($httpMethod);
        if (!$isOk)
        {
            $error = $this->getValidatorError();
            $error->setParams([
                'Allow' => implode(',', $restApi->allowMethods()),
            ]);
            $this->extendErrorParams();
        }
        return $isOk;
    }
} 