<?php

/**
 * Состояния связи для связи типа "многие-ко-многим"
 * 
 * @author morph
 */
class Model_Mapper_Reference_State_ManyToMany extends 
    Model_Mapper_Reference_State_Abstract
{
    /**
     * Прочие данные полученные из таблицы связей
     * 
     * @var array 
     */
    protected $data;
    
    /**
     * Фильтры перед загрузкой
     * 
     * @var array
     */
    protected $preFilters = array();
    
    /**
     * Добавляет модель в коллекцию
     * 
     * @param Model $model
     * @param boolean $mustLoad
     * @param array $data
     * @return Model_Mapper_Reference_State_Abstract
     */
    public function add($model, $mustLoad = false, $data = array())
    {
        $modelScheme = $this->getService('modelScheme');
        $keyField = $modelScheme->keyField($this->dto->modelName);
        if ($mustLoad) {
            if (!$this->collection) {
                $this->load();
            }
            if ($this->collection->filter(array($keyField => $model->key()))) {
                return $this;
            }
            $this->collection->add($model);
        }
        $queryBuilder = $this->getService('query');
        $dds = $this->getService('dds');
        $existsQuery = $queryBuilder
            ->select($this->dto->toJoinField)
            ->from($this->dto->JoinTable)
            ->where($this->dto->fromJoinField, $this->model->$this->dto->fromField)
            ->where($this->dto->toJoinField, $model->$this->dto->toField)
            ->limit(1);
        $exists = $dds->execute($existsQuery)->getResult()->asRow();
        if ($exists) {
            return $this;
        }
        $query = $queryBuilder
            ->insert($this->dto->JoinTable)
            ->values(
                array_merge($data, array(
                    $this->dto->fromJoinField   => $this->model->$this->dto->fromField,
                    $this->dto->toJoinField   => $model->$this->dto->toField
                ))
             );
        $dds->execute($query);
        return $this;
    }
    
    /**
     * @inheritdoc
     */
    public function all()
    {
        $collection = parent::all();
        if ($this->data) {
            $fieldName = $this->dto->toJoinField;
            foreach ($collection as $item) {
                $item->data($this->data[$item[$fieldName]]);
            }
        }
        return $collection;
    }
    
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
     * Удаляет модель
     * 
     * @param Model $model
     * @param boolean $mustLoad
     * @return Model_Mapper_Reference_State_Abstract
     */
    public function delete($model, $mustLoad = false)
    {
        if ($mustLoad) {
            if (!$this->collection) {
                $this->load();
            }
            foreach ($this->collection as $i => $item) {
                if ($item->key() == $model->key()) {
                    $this->collection->unset($i);
                }
            }
        }
        $queryBuilder = $this->getService('query');
        $dds = $this->getService('dds');
        $query = $queryBuilder
            ->delete()
            ->from($this->dto->JoinTable)
            ->where($this->dto->fromJoinField, $this->model->key());
        $dds->execute($query);
        return $this;
    }
    
    /**
     * Получить схему для таблицы связи
     * 
     * @param array $joinColumn
     */
    protected function getSchemeForJoinTable()
    {
        $dto = $this->getService('dto')->newInstance('Model_Scheme');
        $dto->setFields(array(
            'id'                            => array(
                'Int', array(
                    'Size'      => 11,
                    'Not_Null',
                    'Auto_Increment'
                )
            ),
            $this->dto->toJoinField       => array(
                'Int', array(
                    'Size'  => 11,
                    'Not_Null'
                )
            ),
            $this->dto->fromJoinField    => array(
                'Int', array(
                    'Size'  => 11,
                    'Not_Null'
                )
            ) 
        ));
        $dto->setIndexes(array(
            'id'                            => array('Primary', array('id')),
            $this->dto->toJoinField       => array(
                'Key', array($this->dto->toJoinField)
            ),
            $this->dto->fromJoinField    => array(
                'Key', array($this->dto->fromJoinField)
            )
        ));
        return $dto;
    }
    
    /**
     * @inheritdoc
     */
    public function getCollection()
    {
        $modelScheme = $this->getService('modelScheme');
        $keyField = $modelScheme->keyField($this->dto->modelName);
        $this->collection = $this->collection();
        $joinTableFields = $modelScheme->scheme($this->dto->JoinTable)->fields;
        
        if (!$joinTableFields) {
            $dto = $this->getSchemeForJoinTable();
            $this->getService('helperModelScheme')->create(
                $this->dto->JoinTable, $dto
            );
            $this->getService('helperModelTable')->create(
                $this->dto->JoinTable
            );
        } else {
            $queryBuilder = $this->getService('query');
            $dds = $this->getService('dds');
            $fields = array_keys($joinTableFields->__toArray());

            unset($fields[$modelScheme->keyField($this->dto->JoinTable)]);
            unset($fields[$this->dto->toField]);
            $query = $queryBuilder
                ->select('*')
                ->from($this->dto->JoinTable)
                ->where($this->dto->fromJoinField, $this->model->key());
        
            if ($this->preFilters) {
                foreach ($this->preFilters as $fieldName => $value) {
                    $query->where($fieldName, $value);
                }
            }
            $data = $dds->execute($query)->getResult()->asTable(
                $this->dto->toJoinField
            );
            
            $ids = array_keys($data);
            
            $this->data = count($fields) > 1 ? $data : array();
            
            $this->collection->query()->where($keyField, $ids);
        }
        parent::load();
        return $this->collection;
    }
    
    /**
     * @inheritdoc
     */
    public function raw($columns = array())
    {
        $items = parent::raw($columns);
        if ($this->data) {
            $fieldName = $this->dto->toField;
            $modelScheme = $this->getService('modelScheme');
            $joinTableFields = $modelScheme->scheme($this->dto->JoinTable)
                ->fields;
            $fields = array_keys($joinTableFields->__toArray());
            unset($fields[$modelScheme->keyField($this->dto->JoinTable)]);
            unset($fields[$this->dto->toField]);
            $fieldsToSelect = array();
            if (!$columns) {
                $fieldsToSelect = $fields;
            } elseif (($intersect = array_intersect($columns, $fields))) {
                $fieldsToSelect = $intersect;
            }
            if ($fieldsToSelect) {
                foreach ($items as $i => $item) {
                    $items[$i] = array_merge(
                        $item, $this->data[$item[$fieldName]]
                    );
                }
            }
        }
        return $items;
    }
    
    /**
     * @inheritdoc
     */
    public function registerFilter($field, $value)
    {
        if (strpos($field, '::') === 0) {
            $field = substr($field, 2);
            if (!$this->validateField($this->dto->JoinTable, $field)) {
                return null;
            }
            $this->preFilters[$field] = $value;
        } else {
            if (!$this->validateField($this->model->modelName(), $field)) {
                return null;
            }
            $this->filters[$field] = $value;
        }
    }
    
    /**
     * Обновить данные связи
     * 
     * @param array $data
     * @param Model $model
     * @return Model_Mapper_Reference_State_Abstract
     */
    public function update($data, $model = null)
    {
        $queryBuilder = $this->getService('query');
        $dds = $this->getService('dds');
        if ($model) {
            $modelId = $model->key();
        } else {
            if (!$this->collection) {
                $this->load();
            }
            $modelId = $this->collection->column($this->dto->JoinColumn['on']);
        }
        $query = $queryBuilder
            ->update($this->dto->JoinTable)
            ->values($data)
            ->where($this->dto->fromJoinField, $this->model->key())
            ->where($this->dto->toJoinField, $modelId);
        $dds->execute($query);
        return $this;
    }
}