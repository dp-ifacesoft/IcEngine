<?php

/**
 * Состояния связи для связи типа "один-ко-многим"
 * 
 * @author morph
 */
class Model_Mapper_Reference_State_OneToMany extends 
    Model_Mapper_Reference_State_Abstract
{
    /**
     * @inhertdoc
     * 
     * @return Model_Collection
     */
    public function collection()
    {
        return $this->getService('collectionManager')->create(
            $this->dto->modelName);
    }
    
    /**
     * @inheritdoc
     */
    public function getCollection()
    {
        $fromField = $this->dto->fromField; 
        $this->collection = $this->collection();
        $this->collection->query()->where(
                $this->dto->toField, $this->model->$fromField
        );
        parent::load();
        return $this->collection;
    }
}