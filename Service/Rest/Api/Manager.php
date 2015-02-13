<?php
/**
 * Менеджер сервисов REST API
 *
 * @author LiverEnemy
 */

class Service_Rest_Api_Manager extends Manager_Simple
{
    /**
     * @inheritdoc
     *
     * Если нам не удалось найти специально заточенный сервис под конкретную таблицу и мы
     * возвращаем универсальный дефолтный Service_Rest_Api_Default,
     * то зададим ему предварительно модель для работы, чье имя будет равно $name.
     *
     * @return Service_Rest_Api
     */
    public function get($name, $default = null)
    {

        $object = parent::get($name, $default);
        if ($object instanceof Service_Rest_Api_Default)
        {
            $object->setModelName($name);
        }
        return $object;
    }
} 