<?php

/**
 * Тип ссылки "один-ко-многим"
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Reference_OneToMany extends
Model_Mapper_Reference_Abstract
{

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $dto = $this->getService('dto')->newInstance()
                ->set(array(
            'fromField' => $this->args['links']['fromField'],
            'toField' => $this->args['links']['toField'],
            'modelName' => $this->getName()
        ));
        return new Model_Mapper_Reference_State_OneToMany($this->model, $dto);
    }

    /**
     * @inheritdoc
     */
    public function setArgs($args)
    {
    if (!isset($args['links'])) {
            $args  = $args['links'];
        }
        parent::setArgs($args);
    }

}
