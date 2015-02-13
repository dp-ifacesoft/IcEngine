<?php

/**
 * Менеджер событий модели
 * 
 * @author morph
 * @Service("modelEventManager")
 */
class Model_Event_Manager extends Manager_Abstract
{
    /**
     * Зарегистрированные модели
     * 
     * @var array
     * @Generator
     */
    protected $models = array();
    
    /**
     * Регистрация модели
     * 
     * @param Model $model
     */
    public function register($model)
    {
        $eventManager = $this->getService('eventManager');
        $modelName = $model->modelName();
        $id = $model->key();
        $this->models[$modelName . '_' . $id] = $model;
        $annotations = $model->getAnnotations()['properties'];
        foreach ($annotations as $propertyName => $data) {
            if (!isset($data['Event\\On'])) {
                continue;
            }
            $data = reset($data['Event\\On']);
            $slot = new Event_Slot('Slot_' . $modelName . '_' . $id);
            $slot->setDelegee('Helper_Model_Event::process');
            $params = array(
                'initialModel'  => $model,
                'fieldName'     => $propertyName,
                'service'       => $data[1]
            );
            $slot->setParams($params);
            $signal = $eventManager->getSignal($data[0]);
            $eventManager->register($signal, $slot);
        }
    }
    
    /**
     * Сохранить зарегистрированные модели
     */
    public function flush()
    {
        foreach ($this->models as $model) {
            $model->save();
        }
    }
    
    /**
     * Getter for "models"
     *
     * @return array
     */
    public function getModels()
    {
        return $this->models;
    }
        
    /**
     * Setter for "models"
     *
     * @param array models
     */
    public function setModels($models)
    {
        $this->models = $models;
    }
    
}