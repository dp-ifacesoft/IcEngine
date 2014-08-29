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
        $keyField = $modelScheme->keyField($this->dto->Target);
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
            ->select($this->dto->JoinColumn[0])
            ->from($this->dto->JoinTable)
            ->where($this->dto->JoinColumn[0], $this->model->key())
            ->where($this->dto->JoinColumn['on'], $model->key())
            ->limit(1);
        $exists = $dds->execute($existsQuery)->getResult()->asRow();
        if ($exists) {
            return $this;
        }
        $query = $queryBuilder
            ->insert($this->dto->JoinTable)
            ->values(
                array_merge($data, array(
                    $this->dto->JoinColumn[0]       => $this->model->key(),
                    $this->dto->JoinColumn['on']    => $model->key()
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
            $fieldName = $this->dto->JoinColumn['on'];
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
            $this->dto->Target);
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
            ->where($this->dto->JoinColumn[0], $this->model->key())
            ->where($this->dto->JoinColumn['on'], $model->key());
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
            $this->dto->JoinColumn[0]       => array(
                'Int', array(
                    'Size'  => 11,
                    'Not_Null'
                )
            ),
            $this->dto->JoinColumn['on']    => array(
                'Int', array(
                    'Size'  => 11,
                    'Not_Null'
                )
            ) 
        ));
        $dto->setIndexes(array(
            'id'                            => array('Primary', array('id')),
            $this->dto->JoinColumn[0]       => array(
                'Key', array($this->dto->JoinColumn[0])
            ),
            $this->dto->JoinColumn['on']    => array(
                'Key', array($this->dto->JoinColumn['on'])
            )
        ));
        return $dto;
    }
    
    /**
     * @inheritdoc
     */
    public function load()
    {
        $modelScheme = $this->getService('modelScheme');
        $keyField = $modelScheme->keyField($this->dto->Target);
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
            unset($fields[$this->dto->JoinColumn[0]]);
            $query = $queryBuilder
                ->select($fields)
                ->from($this->dto->JoinTable)
                ->where($this->dto->JoinColumn[0], $this->model->key());
            if ($this->preFilters) {
                foreach ($this->preFilters as $fieldName => $value) {
                    $query->where($fieldName, $value);
                }
            }
            $data = $dds->execute($query)->getResult()->asTable(
                $this->dto->JoinColumn['on']
            );
            $ids = array_keys($data);
            $this->data = count($fields) > 1 ? $data : array();
            $this->collection->query()->where($keyField, $ids);
        }
        parent::load();
    }
    
    /**
     * @inheritdoc
     */
    public function one()
    {
        $model = parent::one();
        if ($this->data) {
            $fieldName = $this->dto->JoinColumn['on'];
            $model->data($this->data[$model[$fieldName]]);
        }
        return $model;
    }
    
    /**
     * @inheritdoc
     */
    public function raw($columns = array())
    {
        $items = parent::raw($columns);
        if ($this->data) {
            $fieldName = $this->dto->JoinColumn['on'];
            $modelScheme = $this->getService('modelScheme');
            $joinTableFields = $modelScheme->scheme($this->dto->JoinTable)
                ->fields;
            $fields = array_keys($joinTableFields->__toArray());
            unset($fields[$modelScheme->keyField($this->dto->JoinTable)]);
            unset($fields[$this->dto->JoinColumn[0]]);
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
            ->where($this->dto->JoinColumn[0], $this->model->key())
            ->where($this->dto->JoinColumn['on'], $modelId);
        $dds->execute($query);
        return $this;
    }
}