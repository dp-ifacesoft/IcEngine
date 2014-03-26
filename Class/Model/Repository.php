<?php

/**
 * Репозиторий модели
 *
 * @author morph
 */
class Model_Repository
{
    /**
     * Текущая модель
     *
     * @var Model
     * @Generator
     */
    protected $model;

    /**
     * @see Model::__call
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->model, $method), $args);
    }

    /**
     * @see Model::__get
     */
    public function __get($key)
    {
        return $this->model->__get($key);
    }

    /**
     * @see Model::__set
     */
    public function __set($key, $value)
    {
        $this->model->__set($key, $value);
        return $this->model;
    }

    /**
     * Получить сервис по имени
     *
     * @param string $serviceName
     * @return mixed
     */
    protected function getService($serviceName)
    {
        return $this->model->getService($serviceName);
    }

    /**
     * Getter for "model"
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Setter for "model"
     *
     * @param Model model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

}