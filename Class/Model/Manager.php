<?php

/**
 * Менеджер моделей
 *
 * @author goorus, morph, neon
 * @Service("modelManager")
 */
class Model_Manager extends Manager_Abstract
{
	/**
	 * @inheritdoc
	 */
	protected $config = array(
		'delegee'	=> array(
			'Model'             => 'Simple',
			'Component'         => 'Simple',
			'Model_Defined'		=> 'Defined',
			'Model_Factory'		=> 'Factory',
            'Model_Sync'        => 'Simple'
		),
        'heavyDelegees'         => array(
            'Model_Factory', 'Model_Defined'
        )
	);

    /**
     * Созданные делегаты
     *
     * @var array
     */
    protected $delegees;

	/**
	 * Получение модели по первичному ключу
     *
	 * @param string $modelName Имя класса модели.
	 * @param integer $key Значение первичного ключа.
     * @param boolean $lazy Добавить ли загрузку модели в очередь отложенных
     * загрузок
	 * @return Model|null
	 */
	public function byKey($modelName, $key, $lazy = false)
	{
		$locator = IcEngine::serviceLocator();
        $resourceManager = $locator->getService('resourceManager');
        $result = $resourceManager->get('Model', $modelName . '__' . $key);
        if ($result) {
            return $result;
        }
        $modelScheme = $locator->getService('modelScheme');
        $keyField = $modelScheme->keyField($modelName);
        $queryBuilder = $locator->getService('query');
        if (!$lazy) {
            $query = $queryBuilder->where($keyField, $key);
            return $this->byQuery($modelName, $query);
        }
        $model = $this->create($modelName, array());
        $model->set($keyField, $key);
        $query = $queryBuilder->select('*')->where($keyField, $key);
        $locator->getService('unitOfWork')->push($query, $model, 'Simple');
        $model->setLazy(true);
        return $model;
	}

    /**
     * Получение модели по опциям
     *
     * @param string $modelName Название модели.
     * @internal param mixed $_ [optional]
     * @return Model|null
     */
	public function byOptions($modelName)
	{
        $collectionManager = $this->getService('collectionManager');
        $collection = $collectionManager->create($modelName)
            ->addOptions(array(
                'name'  => '::Limit',
                'count' => 1
            ));
        $args = func_get_args();
        $count = count($args);
        if ($count > 1) {
            if ($count == 2 && is_array($args[1]) && !empty($args[1][0]) &&
                is_array($args[1][0])) {
                $args = $args[1];
                array_unshift($args, null);
                $count = count($args);
            }
            for ($i = 1; $i < $count; $i++) {
                $collection->addOptions($args[$i]);
            }
        }
        $model = $collection->first();
        return $model;
	}

	/**
	 * Получение модели по запросу
     *
	 * @param string $modelName Название модели.
	 * @param Query_Abstract $query Запрос.
     * @param boolean $lazy Добавить ли запрос на загрузку о очередь
     * отложенных запросов
	 * @return Model|null
	 */
	public function byQuery($modelName, Query_Abstract $query, $lazy = false)
	{
        $config = $this->config();
        $parentClass = $this->getParentClass($modelName, $config['delegee']);
        $heavyDelegees = $config->heavyDelegees->__toArray();
        if (!$query->getPart(Query::SELECT)) {
            $query->select(array($modelName => '*'));
        }
        if (!$query->getPart(Query::FROM)) {
            $query->from($modelName);
        }
		if ($lazy && !in_array($parentClass, $heavyDelegees)) {
            return $this->lazyLoad($modelName, $query);
        }
        $modelScheme = $this->getService('modelScheme');
        $dataSource = $modelScheme->dataSource($modelName);
        $data = $dataSource->execute($query)->getResult()->asRow();
        $keyField = $modelScheme->keyField($modelName);
        if (!$data || !isset($data[$keyField])) {
            return null;
        }
        $key = $data[$keyField];
        $scheme = $modelScheme->scheme($modelName)->fields;
        foreach (array_keys($scheme->__toArray()) as $fieldName) {
            if (isset($data[$fieldName])) {
                continue;
            }
            unset($data[$fieldName]);
        }
        $model = $this->get($modelName, $key, $data);
        if ($lazy) {
            $model->setLazy(true);
        }
        return $model;
	}
     
	/**
	 * Создать модель из источника
     *
	 * @param string $modelName
	 * @param array $fields источник значений для полей
	 * @return Model
	 */
	public function create($modelName, $fields)
	{
        $modelScheme = $this->getService('modelScheme');
		$scheme = $modelScheme->scheme($modelName);
		$schemeFields = $scheme->fields;
		$row = array();
		foreach ($schemeFields as $field => $data) {
            $default = isset($data['Default']) ? $data['Default'] : null;
            $value = isset($fields[$field]) ? $fields[$field] : $default;
            $row[$field] = $value;
        }
		$config = $this->config();
        $parent = $this->getParentClass($modelName, $config['delegee']);
		$delegee = 'Model_Manager_Delegee_' . $config['delegee'][$parent];
		if ($config['delegee'][$parent] == 'Simple') {
            $newModel = new $modelName($row);
        } else {
            if (!isset($this->delegees[$delegee])) {
                $this->delegees[$delegee] = new $delegee;
            }
            $delegee = $this->delegees[$delegee];
            $newModel = call_user_func_array(
                array($delegee, 'get'),
                array($modelName, 0, $row)
            );
        }
		$newModel->set($row);
		return $newModel;
	}

	/**
	 * Получение данных модели
     *
	 * @param string $modelName Название модели
	 * @param string $key Ключ (id)
	 * @param Model|array $source Объект или данные
	 * @return Model В случае успеха объект, иначе null.
	 */
	public function get($modelName, $key, $source = null)
	{
		if ($source instanceof Model) {
            return $source;
        }
        $resourceManager = $this->getService('resourceManager');
        $resourceKey = $modelName . '__' . $key;
        $model = $resourceManager->get('Model', $resourceKey);
        if ($model instanceof Model) {
            return $model;
        }
        $config = $this->config();
        $parent = $this->getParentClass($modelName, $config['delegee']);
        $delegee = 'Model_Manager_Delegee_' .
            $config['delegee'][$parent];
        if ($config['delegee'][$parent] == 'Simple') {
            $newModel = new $modelName($source ?: array());
        } else {
            if (!isset($this->delegees[$delegee])) {
                $this->delegees[$delegee] = new $delegee;
            }
            $delegee = $this->delegees[$delegee];
            $newModel = call_user_func_array(
                array($delegee, 'get'),
                array($modelName, $key, $source)
            );
        }
        $keyField = $newModel->keyField();
        $newModel->set($keyField, $key);
        if (!$key) {
            $this->read($newModel);
            $resourceKey = $modelName . '__' . $newModel->key();
        }
        $resourceManager->set('Model', $resourceKey, $newModel);
		return $newModel;
	}

    /**
     * Получить имя родительского класса
     *
     * @param string $modelName
     * @param array|Objective $config
     * @return string
     */
    public function getParentClass($modelName, $config)
    {
        $parents = class_parents($modelName);
        $config = $this->config();
        foreach ($parents as $parent) {
            if (isset($config['delegee'][$parent])) {
                return $parent;
            }
        }
    }

    /**
     * Отложенная загрузка модели по запросу
     *
     * @param string $modelName
     * @param Query_Abstract $query
     * @return Model
     */
    public function lazyLoad($modelName, $query)
    {
		$where = $query->part(QUERY::WHERE);
		$wheres = array();
		$whereFields = array();
		foreach ($where as $value) {
			$wheres[] = $value[QUERY::WHERE] . '=' . $value[QUERY::VALUE];
			$whereFields[$value[QUERY::WHERE]] = $value[QUERY::VALUE];
		}
        $resourceManager = $this->getService('resourceManager');
		$keyHash = implode(':', $wheres);
		$result = $resourceManager->get('Model', $modelName . '__' . $keyHash);
		if ($result) {
			return $result;
		}
        static $filters = array(
            '?'	=> '',
            '<'	=> '',
            '>'	=> ''
        );
		$whereFieldsPrepared = array();
		foreach($whereFields as $key => $whereField) {
			$fieldName = trim(strtr($key, $filters));
			$whereFieldsPrepared[$fieldName] = $whereField;
		}
		$model = $this->modelManager->create($modelName);
        $model->set($whereFieldsPrepared);
		$this->getService('unitOfWork')->push($query, $model, 'Simple');
		return $model;
    }

    /**
	 * Получение данных модели из источника данных.
	 *
     * @param Model $model
	 * @return boolean
	 */
	protected function read(Model $model)
	{
		$key = $model->key();
		if (!$key) {
			return false;
		}
        $modelName = $model->table();
        $queryBuilder = $this->getService('query');
		$query = $queryBuilder
			->select ('*')
			->from($modelName)
			->where($model->keyField(), $key);
        $modelScheme = $this->getService('modelScheme');
        $dataSource = $modelScheme->dataSource($modelName);
        $data = $dataSource->execute($query)->getResult()->asRow();
        if ($data) {
            $data = array_merge($data, $model->asRow());
            $model->set($data);
            return true;
        }
        return false;
	}

	/**
	 * Удаление данных модели из источника
     *
	 * @param Model $model
	 */
	public function remove(Model $model)
	{
        $key = $model->key();
        if (!$key) {
            return;
        }
        $resourceManager = $this->getService('resourceManager');
        $resourceManager->set('Model', $model->resourceKey(), null);
        $modelName = $model->table();
        $modelScheme = $this->getService('modelScheme');
        $queryBuilder = $this->getService('query');
        $dataSource = $modelScheme->dataSource($modelName);
        $query = $queryBuilder
            ->delete()
            ->from($modelName)
            ->where($model->keyField(), $key);
        $dataSource->execute($query);
	}

	/**
	 * Сохранение данных модели
     *
	 * @param Model $model Объект модели.
	 * @param boolean $hardInsert Объект будет вставлен в источник данных.
	 */
	public function set(Model $model, $hardInsert = false)
	{
        $resourceKey = $model->resourceKey();
        $updatedFields = $model->getUpdatedFields();
        if (!$model->key() || $hardInsert) {
            $updatedFields = $model->getFields();
            $model->setUpdatedFields($updatedFields);
        }
        if ($updatedFields) {
            $this->write($model, $hardInsert);
        }
        $model->setUpdatedFields(array());
        $resourceManager = $this->getService('resourceManager');
		$resourceManager->set('Model', $resourceKey, $model);
		$resourceManager->setUpdated('Model', $resourceKey, $updatedFields);
	}

    /**
	 * Сохранение модели в источник данных
     *
	 * @param Model $model
	 * @param boolean $hardInsert
	 */
	protected function write(Model $model, $hardInsert = false)
	{
        $modelName = $model->table();
        $key = $model->key();
        $keyField = $model->keyField();
        $modelScheme = $this->getService('modelScheme');
        $dataSource = $modelScheme->dataSource($modelName);
        $queryBuilder = $this->getService('query');
        $modelFields = $model->getFields();
        $schemeFields = $modelScheme->scheme($modelName)->fields;
        foreach (array_keys($modelFields) as $fieldName) {
            if (isset($schemeFields[$fieldName])) {
                continue;
            }
            unset($modelFields[$fieldName]);
        }
        if ($key && !$hardInsert) {
            $query = $queryBuilder
                ->update($modelName)
                ->values($modelFields)
                ->where($keyField, $key)
				->limit(1);
            $dataSource->execute($query);
        } else {
            if (!$key) {
                $key = $modelScheme->generateKey($model);
            }
            if ($key) {
                $model->set($keyField, $key);
                $modelFields[$keyField] = $key;
            } else {
                $model->unsetField($keyField);
                if (isset($modelFields[$keyField])) {
                    unset($modelFields[$keyField]);
                }
            }
            $query = $queryBuilder
                ->insert($modelName)
                ->values($modelFields);
            $result = $dataSource->execute($query)->getResult();
            if (!$key) {
                $key = $result->insertId();
                $model->set($keyField, $key);
            }
        }
	}
}
