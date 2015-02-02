<?php
/**
 * Абстрактный валидатор REST API сервисов
 *
 * @author LiverEnemy
 */

abstract class Validator_Rest_Api extends Validator
{
    /**
     * Расширить параметры ошибки валидации - скопировать их туда из валидатора
     *
     * @return $this
     */
    public function extendErrorParams()
    {
        $restApi = $this->getData();
        $action = $restApi->getAction();
        $error = $this->getValidatorError();
        $errorParams = $error->getParams() ?: [];
        $error->setParams(
            array_merge(
                $errorParams,
                [
                    'service'   => get_class($restApi),
                    'action'    => $action,
                ]
            )
        );
        return $this;
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
     * @inheritdoc
     * @param Service_Rest_Api $data
     * @throws Exception В случае, если на вход подан не экземпляр Service_Rest_Api
     */
    public function setData($data)
    {
        if (!($data instanceof Service_Rest_Api))
        {
            throw new Exception(__METHOD__ . ' requires an argument to be an instance of Service_Rest_Api');
        }
        return parent::setData($data);
    }
} 