<?php

/**
 * Валидация ключа входящего транспорта на пустое значение
 * 
 * @author morph
 */
class Controller_Validator_Not_Null extends Controller_Validator_Abstract
{
    /**
     * @inheritdoc
     */
    public function validate($params)
    {
        $params = array_values($params);
        $controller = $this->context->getController();
        $paramValue = $controller->getInput()->receive($params[0]);
        if (!$paramValue) {
            return $this->sendError('null');
        }
        return true;
    }
}