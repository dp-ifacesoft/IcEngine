<?php
/**
 * Валидатор проверки сервиса REST API на корректность названия целевого метода
 *
 * Название целевого метода НЕ ДОЛЖНО ссылаться на служебные методы сервиса.
 *
 * @author LiverEnemy
 */

class Validator_Rest_Api_Action_Correct extends Validator_Rest_Api
{
    public function validate()
    {
        $restApi = $this->getData();
        $class = $restApi;
        $prohibitedActions = $restApi::$prohibitedActions;
        while (($class = get_parent_class($class)) && ($class instanceof Service_Rest_Api))
        {
            $prohibitedActions = array_merge(
                $prohibitedActions,
                $class::$prohibitedActions
            );
        }
        $action = $restApi->getAction();
        $isOk = !in_array($action, $prohibitedActions);
        if (!$isOk)
        {
            $this->extendErrorParams();
        }
        return $isOk;
    }
} 