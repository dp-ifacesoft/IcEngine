<?php

/**
 * Валидация контроллера на предмет существования указанной модели с первичным
 * ключом, полученным из указанной переменной входящего транспорта
 * 
 * @author morph
 */
class Controller_Validator_Model_Exists extends Controller_Validator_Abstract
{
    /**
     * @inheritdoc
     */
    public function validate($params)
    {
        $params = array_values($params);
        $controller = $this->context->getController();
        $paramValue = $controller->getInput()->receive($params[1]);
        $model = $this->getService('modelManager')->byKey(
            $params[0], $paramValue
        );
        if (!$model) {
            return $this->notFound();
        }
    }
}