<?php

/**
 * Валидация того, что экшин может быть вызван только через ajax
 * 
 * @author morph
 */
class Controller_Validator_Ajax extends Controller_Validator_Abstract
{
    /**
     * @inheritdoc
     */
    public function validate($params)
    {
        $request = $this->getService('request');
        if (!$request->isAjax() && !$request->isJsHttpRequest()) {
            return $this->accessDenied();
        }
        return true;
    }
}