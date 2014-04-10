<?php

/**
 * Валидация контроллера на предмет REQUEST_METHOD
 * 
 * @author morph
 */
class Controller_Validator_Request_Method extends Controller_Validator_Abstract
{
    /**
     * @inheritdoc
     */
    public function validate($params)
    {
        $requestMethod = strtolower(reset($params));
        $request = $this->getService('request');
        if ($requestMethod != strtolower($request->requestMethod())) {
            return $this->accessDenied();
        }
        return true;
    }
}