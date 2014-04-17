<?php

/**
 * Менеджер атрибутов. Получает и устанавливает значения атрибутов модели
 * 
 * @author goorus, morph
 * @Service("attributeManager")
 */
class Attribute_Manager extends Manager_Abstract
{
	/**
	 * Разделитель для формирования ключа.
	 * 
     * @var string
	 */
	const DELIM = '/';

	/**
	 * Таблица аттрибутов
	 * 
     * @var string
	 */
    const TABLE = 'Attribute';

	/**
	 * @inheritdoc
	 */
	protected $config = array(
		// Источник
		'source'		=> null,
		// Провайдер, используемый для кэширования
		'provider'		=> null,
        // Таблица атрибутов
        'tables'        => array()
	);
    
    /**
     * Инициализирован ли атрибут менеджер
     */
    protected $isInited = false;

	/**
	 * Провайдер для кэширования
	 * 
     * @var Data_Provider_Abstract
	 */
	protected $provider;

    /**
	 * Место хранения аттрибутов
	 * 
     * @var Data_Source_Abstract
	 */
	protected $source;
    
	/**
	 * Инициализация
	 */
	public function init()
	{
        if ($this->isInited) {
            return;
        }
        $this->isInited = true;
		$config = $this->config();
		if ($config['source']) {
			$this->source = $this->getService('dataSourceManager')->get(
                $config['source']
            );
		} else {
			$this->source = $this->getService('dds')->getDataSource();
		}
		if ($config ['provider']) {
			$this->provider = $this->getService('dataProviderManager')->get(
				$config['provider']
			);
		}
	}

	/**
	 * Удаляет все атрибуты модели
	 * 
     * @param Model $model
	 */
	public function deleteFor(Model $model)
	{
        $this->init();
        $modelName = $model->table();
		$source = $this->getSource($modelName);
        $queryBulder = $this->getService('query');
        $table = $this->getTable($modelName);
        $deleteQuery = $queryBulder
            ->delete()
            ->from($table)
            ->where('table', $modelName)
            ->where('rowId', $model->key());
        $source->execute($deleteQuery);
        if ($this->provider) {
            $this->provider->deleteByPattern($this->getPattern($model));
        }
	}

	/**
	 * Получение значения атрибута.
	 * 
     * @param Model $model Модель.
	 * @param string $key Название атрибута.
	 * @return mixed Значение атрибута.
	 */
	public function get(Model $model, $key)
	{
        $this->init();
		$modelName = $model->table();
		$id = $model->key();
		if ($this->provider) {
			$providerKey = $this->getPattern($model) . $key;
			$value = $this->provider->get($providerKey);
			if ($value) {
				return !empty($value['v']) ? $value['v'] : null;
			}
		}
		$source = $this->getSource($modelName);
        $queryBuilder = $this->getService('query');
        $table = $this->getTable($modelName);
        $selectQuery = $queryBuilder
			->select('value')
			->from($table)
			->where('table', $modelName)
			->where('rowId', $id)
			->where('key', $key);
        $value = $source->execute($selectQuery)->getResult()->asValue();
        if ($value) {
            $value = json_decode(urldecode($value), true);
        }
        if ($this->provider) {
            $this->storeValue($model, $key, $value);
        }
		return $value;
	}
    
    /**
     * Получить паттерн для провайдера по модели
     * 
     * @param Model $model
     * @return string
     */
    protected function getPattern($model)
    {
        return $model->table () . self::DELIM . $model->key () . self::DELIM;
    }
    
    /**
     * Получить имя модели атрибутов по имени модели-владельца
     * 
     * @param string $modelName
     * @return string
     */
    protected function getTable($modelName)
    {
        $config = $this->config();
        if ($config->tables && $config->tables[$modelName]) {
            return $config->tables[$modelName];
        }
        return self::TABLE;
    }

	/**
     * Получить дата сорс по имени модели
     * 
	 * @param string $modelName
     * @return \Data_Source_Abstract
     */
	protected function getSource($modelName)
	{
		$config = $this->config();
		$sources = $config->sources;
		if ($sources && $sources[$modelName]) {
			$source = $this->getService('dataSourceManager')->get(
                $sources[$modelName]
            );
		} else {
			$source = $this->source;
		}
		return $source;
	}

	/**
	 * Задание значения атрибуту
	 * 
     * @param Model $model Модель.
	 * @param string|array $key Название атрибута.
	 * @param mixed $value Значение атрибута.
	 */
	public function set(Model $model, $key, $value)
	{
        $this->init();
	    $modelName = $model->table();
	    $id = $model->key();
        $queryBuilder = $this->getService('query');
        $table = $this->getTable($modelName);
        $deleteQuery = $queryBuilder
            ->delete()
            ->from($table)
            ->where('table', $modelName)
            ->where('rowId', $id);
        if (!is_array($key)) {
            $deleteQuery->where('key', $key);
            $key = array($key => $value);
        } else {
            $deleteQuery->where('key', array_keys($key));
        }
		$source = $this->getSource($modelName);
	    $source->execute($deleteQuery);
        $values = array(
            'table' => $modelName,
            'rowId' => $id
        );
		foreach ($key as $keyName => $keyValue) {
            $values['key'] = $keyName;
            $values['value'] = urlencode(json_encode($keyValue));
            $insertQuery = $queryBuilder
                ->insert($table)
                ->values($values);
            $source->execute($insertQuery);
            if ($this->provider) {
                $this->storeValue($model, $keyName, $keyValue);
            }
        }
	}
    
    /**
     * Сохраняет полученное значение в провайдер
     * 
     * @param Model $model
     * @param string $key
     * @param array $value
     */
    protected function storeValue($model, $key, $value)
    {
        $providerKey = $this->getPattern($model) . $key;
        $this->provider->set($providerKey, array(
            't' => $model->table(),
            'r' => $model->key(),
            'k' => $key,
            'v' => $value
        ));
    }
}