<?php

class Model_Mapper_Reference_State_OneToOne extends
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

    public function model()
    {
        $fromField = $this->dto->fromField; 
        return $this->getService('modelManager')->byQuery($this->dto->modelName, 
            $this->getService('query')
                ->where($this->dto->toField, $this->model->$fromField));
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
