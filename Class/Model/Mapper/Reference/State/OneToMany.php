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
            $this->dto->Target);
    }
    
    /**
     * @inheritdoc
     */
    public function load()
    {
        $this->collection = $this->collection();
        $this->collection->query()->where(
                $this->dto->JoinColumn, $this->model->key()
            );
        parent::load();
    }
}