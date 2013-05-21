<?php

/**
 * Слот, который возвращает метод драйвера вставки назад, после сохранения 
 * модели
 * 
 * @author morph
 */
class Event_Slot_Model_Driver_Back extends Event_Slot
{
    /**
     * @inheritdoc
     */
    public function action()
    {
        $params = $this->getParams();
        $model = $params['model'];
        $modelName = $model->modelName();
        $serviceLocator = IcEngine::serviceLocator();
        $modelScheme = $serviceLocator->getService('modelScheme');
        $dataSource = $modelScheme->dataSource($modelName);
        $dataDriver = $dataSource->driver();
        $dataDriver->setQueryMethod(
            Query::UPDATE, $params['oldMethod']
        );
        $signalName = 'Model_Driver_Back_' . $modelName;
        $this->getService('eventManager')->getSignal($signalName)
            ->unBind('Model_Driver_Back');
    }
}