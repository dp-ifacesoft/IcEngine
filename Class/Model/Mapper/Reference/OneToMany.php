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
                'Target'        => $this->args['Target'], 
                'JoinColumn'    => $this->args['JoinColumn']
            ));
        return new Model_Mapper_Reference_State_OneToMany($this->model, $dto);
    }
    
    /**
     * @inheritdoc
     */
    public function setArgs($args)
    {
        if (!isset($args['Target'])) {
            $args['Target'] = $this->getService('helperService')->normalizeName(
                $this->field
            );
        }
        if (!isset($args['JoinColumn'])) {
            $modelName = $this->model->modelName();
            $keyField = $this->getService('modelScheme')->keyField($modelName);
            $args['JoinColumn'] = $modelName . '__' . $keyField;
        }
        parent::setArgs($args);
    }
}