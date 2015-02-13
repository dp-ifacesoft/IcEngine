<?php
/**
 * Сервис облегчения жизни в работе с Event-слотами
 *
 * Призван немного уменьшить количество энтропии в этой Вселенной,
 * упростив получение параметров слота в нужном виде (в виде полей модели, объекта модели и т.д.).
 *
 * @Service("serviceEventSlot")
 * 
 * @author LiverEnemy
 */

class Service_Event_Slot extends Service_Abstract
{
    /**
     * Модель, с которой работает слот (обновленная, вставленная, удаленная - в зависимости от слота)
     *
     * @var Model
     */
    protected $_model;

    /**
     * Название класса модели, с которой работает связанный Event-слот
     *
     * @var string
     */
    protected $_modelClass;

    /**
     * Параметры связанного Event-слота
     *
     * @var Mixed
     */
    protected $_params;

    /**
     * Получить название класса модели, с которой работает связанный Event_Slot
     *
     * @return string
     */
    protected function _getModelClass()
    {
        return $this->_modelClass;
    }

    protected function _init()
    {
        $params = $this->getParams();
        $modelClass = $this->_getModelClass();
        if (!empty ($modelClass) && !empty($params['model']) && !($params['model'] instanceof $modelClass)) {
            throw new Exception(__METHOD__ . ' requires a Model param to be an instance of ' . $modelClass);
        }
        /** Если вызван set(), т.е. сигнал setComponentComment. Этот сигнал требует установки
         *  'setComponentComment' => 'Model_Component_Comment' в Config_Event_Manager */
        /** Либо если вызван сигнал afterDelete, установленный в схеме модели Event_Slot'а ссылкой на сигнал
         *  update$modelClass, а update$modelClass при этом верно сконфигурирован, как описано ниже*/
        if (empty($modelClass) && !empty($params['model'])) {
            return $this->_setModel($params['model']);
        }
        if (isset($params['model']) && $params['model'] instanceof $modelClass) {
            return $this->_setModel($params['model']);
        }
        if (!empty($params['id']) && $modelClass) {
            $modelManager = App::modelManager();
            $model = $modelManager->byKey($modelClass, $params['id']);
            return $this->_setModel($model);
        }
        return $this;
    }

    /**
     * Установить новую модель, с которой работает слот
     *
     * @param Model $model Новая текущая модель, с которой работает слот
     * @return $this
     */
    protected function _setModel(Model $model)
    {
        $this->_model = $model;
        return $this;
    }

    /**
     * Получить текущую модель для слота
     *
     * @return Model
     * @throws Exception В случае, если модель не определена
     */
    public function getModel()
    {
        if (empty($this->_model))
        {
            throw new Exception(__METHOD__ . ' requires a model to exist. Probably service was not initialized');
        }
        return $this->_model;
    }

    /**
     * Получить поля модели, с которой работает связанный слот
     *
     * @return array
     * @throws Exception
     */
    public function getModelFields()
    {
        $model = $this->getModel();
        return $model->getFields();
    }

    /**
     * Получить параметры связанного Event-слота
     *
     * @return Mixed
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Установить название класса модели, с которой работает связанный Event_Slot
     *
     * @param string|NULL $modelClass Название класса модели, с которой работает связанный Event_Slot
     * @return $this
     * @throws Exception В случае, если на вход подана не строка
     */
    public function setModelClass($modelClass)
    {
        if (!empty($modelClass) && !is_string($modelClass))
        {
            throw new Exception(__METHOD__ . ' requires a model class name to be a string');
        }
        $this->_modelClass = $modelClass;
        return $this;
    }

    /**
     * Установить параметры связанного Event-слота
     *
     * @param Mixed $params Параметры связанного Event-слота
     * @return $this
     */
    public function setParams($params)
    {
        $this->_params = $params;
        return $this->_init();
    }
} 