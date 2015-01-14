<?php

/**
 * Слот для модерации модели
 *
 * @author morph
 */
class Event_Slot_Model_Moderate extends Event_Slot
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
        static $methods = array(
            Query::INSERT, Query::UPDATE, Query::DELETE
        );
        $oldMethods = array();
        $dataDriverNull = new Data_Driver_Null();
        foreach ($methods as $method) {
            $oldMethods[$method] = $dataDriver->getQueryMethod($method);
            $dataDriver->setQueryMethod(
                $method, array($dataDriverNull, 'executeCommand')
            );
        }
        $scheme = $model->scheme()->__toArray();
        $afterSet = isset($scheme['signals']['afterSet'])
            ?$scheme['signals']['afterSet']
            : array();
        $signalName = 'Model_Driver_Back_' . $modelName;
        if (!in_array($signalName, $afterSet)) {
            array_unshift($afterSet, $signalName);
            $model->scheme()['signals']['afterSet'] = $afterSet;
        }
        $signal = $serviceLocator->getService('eventManager')
            ->getSignal($signalName);
        $signal->setData(array(
            'model'      => $model,
            'oldMethods' => $oldMethods
        ));
        $signal->bind('Model_Driver_Back');
    }
}