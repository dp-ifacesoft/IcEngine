<?php
/**
 * Пул валидаторов REST API сервисов
 *
 * @author LiverEnemy
 */

class Validator_Pool_Rest_Api extends Validator_Pool_Abstract
{
    /**
     * @inheritdoc
     * @return Validator_Error_Rest_Api
     */
    public function error()
    {
        return parent::error();
    }
    /**
     * @inheritdoc
     * @return Service_Rest_Api
     */
    public function getData()
    {
        return parent::getData();
    }

    /**
     * @param Service_Rest_Api $data
     * @throws Exception
     * @return $this
     */
    public function setData($data)
    {
        if ($data instanceof Service_Rest_Api)
        {
            return parent::setData($data);
        }
        throw new Exception(__METHOD__ . ' expects a parameter to be Service_Rest_Api');
    }
} 