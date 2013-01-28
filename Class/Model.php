<?php

/**
 * Базовая модель для всех сущностей
 *
 * @author goorus, morph
 */
abstract class Model implements ArrayAccess
{
    /**
     * Название мета-поля с данными модели
     */
    const DATA_FIELD = 'data';

	/**
	 * Конфигурация модели
     *
	 * @var array|Objective
	 */
	protected static $config;

	/**
	 * Связанные данные
     *
	 * @var array
	 */
	protected $data = array();

    /**
	 * Данные модели
     *
	 * @var array
	 */
	protected $fields;

	/**
	 * Подгруженные объекты
     *
	 * @var array
	 */
	protected $joints = array();

    /**
     * Помощник модели
     *
     * @var Helper_Model
     */
    protected static $helper;

    /**
     * Имя первичного ключа
     */
    protected $keyField;

	/**
	 * Означает, что модель отложенная для true
     *
	 * @var bool
	 */
	protected $lazy;

	/**
	 * Схема модели
     *
	 * @var array
	 */
	protected $scheme;

    /**
     * Локатор сервисов
     *
     * @var Service_Locator
     */
    protected static $serviceLocator;

	/**
	 * Обновленные поля
     *
	 * @var array
	 */
	protected $updatedFields = array();

    /**
     * Действие после выполнения конструктора
     */
    protected function _afterConstruct()
    {

    }

	/**
	 * Создает и возвращает модель
     *
	 * @param array $fields Данные модели
	 */
	public function __construct(array $fields = array())
	{
		$this->fields = $fields;
        if ($fields) {
            foreach ($fields as $fieldName => $value) {
                $this->$fieldName = $value;
            }
        }
        $selfFields = $this->helper()->getVars($this);
        foreach ($selfFields as $fieldName) {
			if (!$fieldName || $fieldName[0] == '_') {
				continue;
			}
            if (!in_array($fieldName, $selfFields)) {
                $this->fields[$fieldName] = $this->$fieldName;
            }
        }
        $this->_afterConstruct();
	}

	/**
	 * Возвращает поле
     *
	 * @param string $field Поле
	 * @return mixed
	 */
	public function __get($field)
	{
        if (is_null($this->fields)) {
            $this->load();
        }
		if ($field == self::DATA_FIELD) {
			return $this->getData();
		}
        $joinField = $field . '__id';
        if (isset($this->joints[$field])) {
            return $this->joints[$field];
        } elseif (array_key_exists($field, $this->fields)) {
            return $this->fields[$field];
        } elseif (array_key_exists($joinField, $this->fields)) {
            return $this->joint($field, $this->fields[$joinField]);
        }
        $references = $this->scheme()->references;
        if (isset($references[$field])) {
            return $this->getService('modelMapper')->scheme($this)->$field;
        }
	}

    /**
	 * Проверяет существует ли поле
     *
     * @param string $key
	 * @return boolean
	 */
	public function __isset($key)
	{
		return isset($this->fields[$key]);
	}

    /**
	 * Изменяет значение поля
     *
	 * @param string $field Поле
	 * @param mixed $value Значение
	 */
	public function __set($field, $value)
	{
        if (!$this->fields) {
            $this->load();
        }
        if ($field == self::DATA_FIELD) {
            return $this->data($value);
        }
        $fields = $this->scheme()->fields;
		if (isset($fields[$field])) {
            $selfFields = $this->helper()->getVars($this);
            if (in_array($field, $selfFields)) {
                $this->$field = $value;
            }
            $this->fields[$field] = $value;
		} else {
            echo $this->table() . PHP_EOL;
			throw new Exception ('Field unexists "' . $field . '".');
		}
	}

    /**
	 * Преобразование к массиву
     *
	 * @return array
	 */
	public function __toArray()
	{
		return array(
			'class'     => get_class($this),
			'model'     => $this->modelName(),
			'fields'    => $this->asRow(),
			'data'      => $this->data
		);
	}

    /**
	 * Возвращает массив, создержащий все поля модели
     *
	 * @return array
	 */
	public function asRow()
	{
		return $this->fields ?: array();
	}

    /**
	 * Возвращает или устанавливает значение атрибута
     *
	 * @param string|array $key Название атрибута или массив пар
	 * (название => значение).
	 * @param mixed $value [optional] Новое значение атрибута.
	 * @return mixed Если не задан второй параметр, возвращает значение
	 * аттрибута, иначе null.
	 */
	public function attr($key, $value = null)
	{
        $attributeManager = $this->getService('attributeManager');
        if (func_num_args() == 1) {
            if (is_scalar($key)) {
                return $attributeManager->get($this, $key);
            }
        } else {
            $key = array($key => $value);
        }
        $attributeManager->set($this, $key, null);
	}

    /**
	 * Имя класса модели
     *
	 * @return string
	 */
	public function className()
	{
		return get_class($this);
	}

	/**
	 * Присоединить сущность
     *
	 * @param string $modelName
	 * @param mixed $key
	 * @return Model Присоединенная модель
	 */
	protected function joint($modelName, $key = null)
	{
		if (!is_null($key)) {
            $modelManager = $this->getService('modelManager');
            $joinedModel = $modelManager->byKey($modelName, $key);
			$this->joints[$modelName] = $joinedModel;
		}
		return $this->joints[$modelName];
	}

    /**
     * Проинициализировать и получить помощник модели
     *
     * @return Model_Helper
     */
    protected function helper()
    {
        if (is_null(self::$helper)) {
            self::$helper = new Helper_Model;
        }
        return self::$helper;
    }

	/**
	 * Возвращает коллекцию связанных компонентов или
	 * элемент коллекции с указанным индексом
     *
	 * @param string $type Тип компонентов
	 * @param integer $index Индекс для получения
	 * @return Model_Collection
	 */
	public function component($type, $index = null)
	{
        $collectionManager = $this->getService('collectionManager');
		$collection = $collectionManager->create('Component_' . $type)
            ->addOptions(
                array(
                    'name'  => '::Table',
                    'table' => $this->table()
                ),
                array(
                    'name'  => '::Row',
                    'id'    => $this->key()
                )
            );
        return !is_null($index) ? $collection->item($index) : $collection;
	}

	/**
	 * Загружает и возвращает конфиг для модели
     *
	 * @return Objective
	 */
	public function config()
	{
		if (!is_object(static::$config)) {
			$configManager = $this->getService('configManager');
            static::$config = $configManager->get(
				get_class($this), static::$config
			);
		}
		return static::$config;
	}

	/**
	 * Устанавливает или получает связанные данные объекта
     *
	 * @param string $key Ключ.
	 * @param mixed $value [optional] Значение (не обязательно).
	 * @return mixed Текущее значение или null.
	 */
	public function data($key, $value = null)
	{
		if (func_num_args()  == 1) {
			if (is_scalar($key)) {
				return isset($this->data[$key]) ? $this->data[$key] : null;
			}
			$this->data = array_merge($this->data, $key);
		} else {
			$this->data[$key] = $value;
		}
	}

	/**
	 * Удаление модели
	 */
	public function delete()
	{
        $modelManager = $this->getService('modelManager');
		if ($this->key()) {
			$modelManager->remove($this);
		}
	}

	/**
	 * Возвращает коллекцию моделей типа $model,
	 * связанных по первичному ключу с этой моделью
	 * В модели $model должно существовать поле "THISMODEL__id",
	 * где THISMODEL - название этой модели.
     *
	 * @param string $modelName
     * @param integer $index
	 * @return Model_Collection
	 */
	public function external($modelName, $index = null)
	{
        $collectionManager = $this->getService('collectionManager');
        $collection = $collectionManager->create($modelName)
            ->addOptions(array(
                'name'  => '::External',
                'table' => $this->table(),
                'id'    => $this->key()
            ));
		return !is_null($index) ? $collection->item($index) : $collection;
	}

	/**
	 * Получение или установка значения
     *
	 * @param string $key Поле
	 * @param mixed $value Значение (не обязательно).
	 * Если указано значение, оно будет записано в поле.
	 * @return mixed Если $value не передан, будет возвращено значение поля.
	 */
	public function field($key)
	{
		if (func_num_args() > 1) {
			$this->__set($key, func_get_arg(1));
		} else {
			return $this->__get($key);
		}
	}

	/**
	 * Получить все хранимые данные модели
	 *
	 * @return array
	 */
	public function &getData()
	{
		return $this->data;
	}

	/**
	 * Получить значения полей. Синоним asRow
     *
	 * @return array
	 */
	public function getFields()
	{
		return $this->fields;
	}

    /**
     * Получить помощника модели
     *
     * @return Model_Helper
     */
    public function getHelper()
    {
        return self::$helper;
    }

    /**
     * Отложена ли модель для загрузки
     *
     * @return boolean
     */
    public function getLazy()
    {
        return $this->lazy;
    }

    /**
     * Получить услугу по имени
     *
     * @param string $serviceName
     * @return mixed
     */
    public function getService($serviceName)
    {
        if (!self::$serviceLocator) {
            self::$serviceLocator = IcEngine::serviceLocator();
        }
        return self::$serviceLocator->getService($serviceName);
    }

    /**
     * Получить текущий сервис локатор
     *
     * @return Service_Locator
     */
    public function getServiceLocator()
    {
        return self::$serviceLocator;
    }

    /**
     * Получить список обновленных полей
     *
     * @return array
     */
	public function getUpdatedFields()
	{
		return $this->updatedFields;
	}

	/**
	 * Проверяет существование поля в модели
     *
	 * @return boolean
	 */
	public function hasField($field)
	{
        if (is_null($this->fields)) {
            $this->load();
        }
        return array_key_exists($field, $this->fields);
	}

	/**
	 * Возвращает значение первичного ключа
     *
	 * @return string|null
	 */
	public function key()
	{
		$keyField = $this->keyField();
        return isset($this->fields[$keyField]) ? $this->fields[$keyField] : null;
	}

	/**
	 * Имя поля первичного ключа
     *
	 * @return string
	 */
	public function keyField()
	{
        if (!$this->keyField) {
            $modelScheme = $this->getService('modelScheme');
            $this->keyField = $modelScheme->keyField($this->table());
        }
		return $this->keyField;
	}

	/**
	 * Имя класса модели. Синоним table
     *
	 * @return string
	 */
	public function modelName()
	{
		return $this->table();
	}

	/**
	 * Проверяет существование поля
     *
	 * @param string $offset Название поля
	 * @return boolean true если поле существует
	 */
	public function offsetExists($offset)
	{
		return $this->hasField($offset);
	}

	/**
	 * @see Model::__get
	 */
	public function offsetGet($offset)
	{
		return $this->__get($offset);
	}

	/**
	 * @see Model::__set
	 */
	public function offsetSet($offset, $value)
	{
		$this->__set($offset, $value);
	}

	/**
	 * Исключение поля из модели
     *
	 * @param string $offset название поля
	 */
	public function offsetUnset($offset)
	{
        if (array_key_exists($offset, $this->fields)) {
            unset($this->fields[$offset]);
        }
	}

    /**
     * Получить данные модели массивом
     * @return array
     */
    public function raw()
    {
        return array_merge($this->asRow(), array(
            'data'      => $this->data
        ));
    }

	/**
	 * Название ресурса модели. Состоит из название модели и первичного ключа
     *
	 * @return string
	 */
	public function resourceKey()
	{
		return $this->table() . '__' . $this->key();
	}

	/**
	 * Сохранение данных модели
     *
	 * @param boolean $hardInsert Принудительное сохранение - даже если уже
     * задано значение первичного ключа
	 * @return Model
	 */
	public function save($hardInsert = false)
	{
		$this->getService('modelManager')->set($this, $hardInsert);
		return $this;
	}

	/**
	 * Получить схему модели
     *
	 * @return Objective
	 */
	public function scheme()
	{
        if (!is_null($this->scheme)) {
            return $this->scheme;
        }
		return $this->getService('modelScheme')->scheme($this->table());
	}

	/**
	 * Установка значений полей без обновления источника.
	 * При использовании этого метод не проверяется сущестовование полей
	 * у модели. Это позволяет установить поля для создаваемой модели,
	 * однако может привести к ошибкам в дальнейшем при сохранении, если
	 * были заданы несуществующие поля.
     *
	 * @param string|array $field Имя поля или массив пар "поле - значение".
	 * @param string $value Значение поля для случае, если первым параметром
	 * передано имя.
	 */
	public function set($field, $value = null)
	{
		$fields = is_array($field) ? $field : array($field => $value);
		$scheme = $this->scheme();
        $data = array();
        $schemeFields = array();
        if ($scheme->fields) {
            $schemeFields = array_keys($scheme->fields->__toArray());
        }
        foreach ($fields as $field => $value) {
            if (!$schemeFields || in_array($field, $schemeFields)) {
                $this->$field = $value;
            } else {
                $data[$field] = $value;
            }
        }
        if ($data) {
            $this->data($data);
        }
	}

    /**
     * Изменить помощник модели
     *
     * @param mixed $helper
     */
    public function setHelper($helper)
    {
        self::$helper = $helper;
    }

    /**
	 * Установить флаг отложенной модели, через Unit Of Work
	 *
	 * @param bool $value
	 */
	public function setLazy($value)
	{
		$this->lazy = $value;
	}

    /**
     * Изменить схему модели
     *
     * @param mixed $scheme
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;
    }

    /**
     * Изменить локатор сервисов
     *
     * @param Service_Locator $serviceLocator
     */
    public function setServiceLocator($serviceLocator)
    {
        self::$serviceLocator = $serviceLocator;
    }

	/**
	 * Тихое получение или установка поля
     *
	 * @param string $key Название поля.
	 * @param mixed $value [optional] Значение поля.
	 * @return mixed Текущее значение поля или null.
	 */
	public function sfield($key)
	{
		return $this->hasField($key) ? $this->fields[$key] : null;
	}

	/**
	 * Таблица БД
     *
	 * @return string
	 */
	public function table()
	{
		return $this->className();
	}

	/**
	 * Возвращает имя сущности
     *
	 * @return string
	 */
	public function title()
	{
		return $this->hasField('title') ? $this->title : null;
	}

	/**
	 * Загрузка данных модели
     *
	 * @param mixed $key Первичный ключ.
	 * @return Model Эта модель.
	 */
	public function load()
	{
        if ($this->lazy) {
            $this->getService('unitOfWork')->load($this);
        } else {
            $modelManager = $this->getService('modelManager');
            $modelManager->get($this->table(), $this->key(), $this);
        }
        if (is_null($this->fields)) {
            $this->fields = array(
                $this->keyField() => null
            );
        }
		return $this;
	}

    /**
     * Валидация модели с использованием схемы валидации
     *
     * @param array|Data_Transport $input
     * @param string $name
     * @return boolean|array
     */
	public function validateWith($input, $name = 'default')
	{
        $scheme = $this->scheme();
        if (!isset($scheme->validators) || !isset($scheme->validators[$name])) {
            return true;
        }
        return $this->getService('modelValidator')->validate(
            $this, $scheme->validators[$name], $input
        );
	}

	/**
	 * Удаляет поле из объекта
	 * Используется в Model_Manager для удаления первичного ключа перед
	 * вставкой в БД
     *
	 * @param string $name Имя поля.
	 * @return Model Эта модель.
	 */
	public function unsetField ($name)
	{
        if ($this->hasField($name)) {
            unset($this->fields[$name]);
        }
		return $this;
	}

	/**
	 * Обновление данных модели и полей в БД
     *
	 * @param array $data Массив пар (поле => значение)
     * @param boolean $hardUpdate
	 * @return Model Эта модель.
	 */
	public function update(array $data, $hardUpdate = false)
	{
		if (is_null($this->fields)) {
            $this->load();
        }
        $fields = $this->scheme()->fields;
        foreach ($data as $key => $value) {
            if (!isset($fields[$key])) {
                continue;
            }
            $this->updatedFields[$key] = $value;
        }
        $this->set($this->updatedFields);
		return $this->save($hardUpdate);
	}
}