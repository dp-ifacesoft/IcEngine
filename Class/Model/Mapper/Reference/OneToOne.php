<?php

/**
 * Тип ссылки "один-к-одному"
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Reference_OneToOne extends 
    Model_Mapper_Reference_Abstract
{
	/**
     * @inheritdoc
	 */
	public function execute()
    {
        return $this->getService('modelManager')->byKey(
            $this->args['Target'], $this->model->key()
        );
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
        parent::setArgs($args);
    }
}